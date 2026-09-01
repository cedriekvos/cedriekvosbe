#!/usr/bin/env python3
"""Pack dispatcher — routes handoffs between agents automatically.

The filesystem is the message bus:
  queue/pending/    handoffs written by agents, routed on sight
  queue/state.json  baton, current task, loop counter, per-agent summaries
  queue/.waiting_*  an agent is blocked on a prompt (written by notify_hook.py)
  runs/<task>/      routed handoffs, archived in sequence — the audit trail

tmux is only the delivery mechanism (send-keys) and the viewport.

Subcommands (normally invoked through pack/bin/pack):
  up       start the tmux session: this TUI + one window per agent
  tui      the overview TUI (run inside tmux by `up`)
  submit   file a new task:  dispatcher.py submit "task text"
  status   one-shot text summary of queue + state
  reset    clear pending queue + state (the runs/ archive is kept)
"""

import fcntl
import json
import os
import re
import select
import shlex
import shutil
import subprocess
import sys
import termios
import time
import tty
from datetime import datetime
from pathlib import Path

try:
    import yaml
except ImportError:
    sys.exit("pack: PyYAML is missing — run `pack/bin/pack setup` first")

PACK_DIR = Path(os.environ.get("PACK_DIR") or Path(__file__).resolve().parent.parent)
PROJECT_DIR = PACK_DIR.parent
QUEUE = PACK_DIR / "queue"
PENDING = QUEUE / "pending"
RUNS = PACK_DIR / "runs"
STATE_FILE = QUEUE / "state.json"
LOCK_FILE = QUEUE / ".lock"
LOG_FILE = QUEUE / "log.txt"
SESSION = "pack"
DONE = "DONE"
HUMAN = "human"
MEDIC = "medic"  # on-call repair agent — outside the flow, summoned from the TUI
SETTLE = 1.5     # seconds a handoff must sit untouched before it is routed

DEFAULT_STATE = {
    "task_seq": 0,   # last task number handed out by `submit`
    "seq": 0,        # handoff counter within the current task (archive ordering)
    "task_id": None,
    "baton": None,   # agent currently expected to be working
    "loop": 1,       # 1 + number of rejections delivered for this task
    "status": "idle",  # idle | working | halted
    "seen": {},      # agent -> task_id last delivered, drives /clear decisions
    "summaries": {},   # agent -> {at, verdict, text}: what it last reported doing
    "halt_reason": None,  # why the pack stopped, shown on the overview
    "notified": {},  # event -> key already pinged, so each one fires once
}


# ---------------------------------------------------------------- plumbing

class Lock:
    """Exclusive lock so `submit`, the TUI, and the Stop hook don't race."""

    def __enter__(self):
        QUEUE.mkdir(parents=True, exist_ok=True)
        self.fh = open(LOCK_FILE, "w")
        fcntl.flock(self.fh, fcntl.LOCK_EX)
        return self

    def __exit__(self, *exc):
        fcntl.flock(self.fh, fcntl.LOCK_UN)
        self.fh.close()


def load_config():
    cfg = yaml.safe_load((PACK_DIR / "pack.yaml").read_text())
    flow = cfg["flow"]
    return {
        "name": cfg.get("pack", {}).get("name") or PROJECT_DIR.name,
        "max_loops": int(cfg.get("pack", {}).get("max_loops", 3)),
        "notify": str(cfg.get("pack", {}).get("notify") or "bell"),
        "entry": flow["entry"],
        "edges": flow["edges"],
        "agents": list(flow["edges"].keys()),
    }


FM_RE = re.compile(r"\A---\s*\n(.*?)\n---\s*\n?(.*)\Z", re.S)


def parse_frontmatter(text):
    m = FM_RE.match(text)
    if not m:
        return None, text
    try:
        return yaml.safe_load(m.group(1)) or {}, m.group(2)
    except yaml.YAMLError:
        return None, text


def norm_task(value):
    s = str(value).strip()
    return f"{int(s):03d}" if s.isdigit() else s


def load_state():
    # copy the nested dicts so per-run mutation never leaks into DEFAULT_STATE
    state = {k: dict(v) if isinstance(v, dict) else v for k, v in DEFAULT_STATE.items()}
    if STATE_FILE.exists():
        state.update(json.loads(STATE_FILE.read_text()))
    return state


def save_state(state):
    STATE_FILE.write_text(json.dumps(state, indent=2) + "\n")


def log(msg):
    QUEUE.mkdir(parents=True, exist_ok=True)
    with open(LOG_FILE, "a") as fh:
        fh.write(f"{datetime.now().strftime('%H:%M:%S')}  {msg}\n")


def tail(path, n):
    if not path.exists():
        return []
    return path.read_text().splitlines()[-n:]


def rel(path):
    return os.path.relpath(path, PROJECT_DIR)


def tmux(*args, check=True):
    return subprocess.run(["tmux", *args], check=check, capture_output=True, text=True)


def session_exists():
    if not shutil.which("tmux"):
        return False
    return subprocess.run(
        ["tmux", "has-session", "-t", SESSION], capture_output=True
    ).returncode == 0


def waiting_note(agent):
    """The prompt an agent is blocked on, or None. notify_hook.py writes these."""
    p = QUEUE / f".waiting_{agent}"
    try:
        at, _, msg = p.read_text().strip().partition("\t")
    except OSError:
        return None
    return (at, msg or "needs your input")


def clear_waiting(agent):
    (QUEUE / f".waiting_{agent}").unlink(missing_ok=True)


def send_line(window, text):
    """Type a line into an agent's claude prompt: literal text, pause, Enter.

    Typing into a window answers whatever it was blocked on, so the waiting
    marker goes with it.
    """
    clear_waiting(window)
    tmux("send-keys", "-t", f"{SESSION}:{window}", "-l", text)
    time.sleep(0.4)
    tmux("send-keys", "-t", f"{SESSION}:{window}", "Enter")


# ---------------------------------------------------------------- routing

SUMMARY_MAX = 110


def handoff_summary(meta, body):
    """One short line on what an agent just did, for the overview.

    The `summary:` field if the agent wrote one, else the opening paragraph of
    its `## Summary` section, squeezed onto a single line.
    """
    text = str(meta.get("summary") or "").strip()
    if not text:
        for section in re.split(r"^##\s+", body, flags=re.M):
            if section.lower().startswith("summary"):
                text = section.split("\n", 1)[-1].strip()
                break
    text = " ".join((text or body).split())
    if len(text) > SUMMARY_MAX:
        text = text[:SUMMARY_MAX - 1].rstrip(" ,.;:") + "…"
    return text


def clear_attempts(agent):
    p = QUEUE / f".attempts_{agent}"
    if p.exists():
        p.unlink()


def archive(path, meta, state):
    """Move an approved handoff into runs/<task>/ with its sequence number."""
    task = norm_task(meta.get("task_id") or state["task_id"] or "000")
    run_dir = RUNS / task
    run_dir.mkdir(parents=True, exist_ok=True)
    state["seq"] += 1
    dest = run_dir / f"{task}-{state['seq']:02d}-{meta.get('from', 'unknown')}.md"
    shutil.move(str(path), dest)
    return dest


def deliver(cfg, state, target, archived, meta, kind):
    """Point an agent at an archived handoff via tmux; /clear first on new tasks."""
    task = state["task_id"]
    pending_path = rel(PENDING / f"{task}-{target}.md")
    handoff_doc = rel(PACK_DIR / "HANDOFF.md")
    if state["seen"].get(target) != task:
        send_line(target, "/clear")
        time.sleep(1.5)
        state["seen"][target] = task
    if kind == "task":
        msg = (f"New task {task}. Read {rel(archived)} and carry it out. When finished, "
               f"write your handoff to {pending_path} following {handoff_doc}.")
    elif kind == "reject":
        msg = (f"Your work on task {task} was rejected (loop {state['loop']}/{cfg['max_loops']}). "
               f"Read {rel(archived)}, address every remark, then write a fresh handoff to "
               f"{pending_path} following {handoff_doc}.")
    else:
        msg = (f"Handoff from {meta.get('from')} for task {task}. Read {rel(archived)} and do "
               f"your part. When finished, write your handoff to {pending_path} "
               f"following {handoff_doc}.")
    send_line(target, msg)
    clear_attempts(target)
    state["baton"] = target
    state["status"] = "working"
    log(f"{kind} → {target}  ({archived.name})")


_NOTIFY_PROCS = []  # in flight notify commands, reaped on the next ping


def notify(cfg, state, event, title, body, key):
    """Ping the human once per distinct event.

    `notify:` in pack.yaml picks how. `bell` (the default) rings the terminal.
    `desktop` also emits OSC 9 and OSC 777 notifications, which terminals like
    iTerm2, WezTerm, Windows Terminal (OSC 9) and foot (OSC 777) turn into real
    desktop notifications — but only while attached, since they travel down the
    tty, and only when your terminal is willing to notify for a window you are
    looking at. Anything else is run as a shell command, with the event in its
    environment, which is how you reach a phone or a machine you are not at:

        notify: 'curl -s -d "$PACK_TITLE: $PACK_BODY" ntfy.sh/my-pack'

    `key` is what makes it fire once: the same event with the same key is
    silent, a new key pings again.
    """
    if state["notified"].get(event) == key:
        return
    state["notified"] = {**state["notified"], event: key}
    how = cfg["notify"]
    sys.stdout.write("\a")
    if how == "desktop":
        # OSC 9 (iTerm2, WezTerm, Windows Terminal) and OSC 777 (foot, urxvt):
        # a terminal ignores whichever one it does not implement. `;` separates
        # OSC 777's fields, so it may not survive inside them.
        def clean(s):
            return s.replace("\033", " ").replace(";", ",")

        head, text = clean(f"{cfg['name']}: {title}")[:80], clean(body)[:200]
        for seq in (f"\033]9;{head} — {text}\007",
                    f"\033]777;notify;{head};{text}\007"):
            # tmux's DCS passthrough, which wants every ESC inside it doubled
            sys.stdout.write("\033Ptmux;" + seq.replace("\033", "\033\033") + "\033\\")
    sys.stdout.flush()
    if how in ("bell", "desktop"):
        return
    env = {**os.environ,
           "PACK_EVENT": event,
           "PACK_NAME": cfg["name"],
           "PACK_TITLE": title,
           "PACK_BODY": body,
           "PACK_TASK": str(state.get("task_id") or "")}
    _NOTIFY_PROCS[:] = [p for p in _NOTIFY_PROCS if p.poll() is None]  # reap
    try:
        _NOTIFY_PROCS.append(subprocess.Popen(
            how, shell=True, env=env, stdin=subprocess.DEVNULL,
            stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL))
    except OSError as exc:
        log(f"notify command failed: {exc}")


def halt(cfg, state, reason):
    """Stop the pack and say why — the overview holds this until [x] clears it."""
    state.update(status="halted", baton=None, halt_reason=reason)
    log(f"HALTED — {reason}")
    notify(cfg, state, "halted", "pack halted", reason, reason)


def route(cfg, state, path, meta, body):
    """Archive one pending handoff and act on its edge. No approval gate:
    whatever an agent files is delivered onward as soon as it lands.

    Caller holds the lock.
    """
    frm = meta.get("from", "unknown")
    verdict = str(meta.get("verdict", "done")).lower()
    if frm == HUMAN:
        target, kind = cfg["entry"], "task"
        state["task_id"] = norm_task(meta.get("task_id"))
        state["seq"] = 0
        state["loop"] = 1
        state["summaries"] = {}  # a new task starts the overview fresh
    else:
        target = (cfg["edges"].get(frm) or {}).get(verdict)
        kind = "reject" if verdict == "reject" else "handoff"
        state["summaries"] = {**state["summaries"], frm: {
            "at": datetime.now().strftime("%H:%M"),
            "verdict": verdict,
            "text": handoff_summary(meta, body),
        }}
        if target is None:
            valid = ", ".join(cfg["edges"].get(frm) or {}) or "none defined"
            halt(cfg, state, f"{frm} filed verdict '{verdict or '(empty)'}' — no such edge in "
                        f"pack.yaml (valid for {frm}: {valid}); handoff left in the queue")
            return
    archived = archive(path, meta, state)
    if target == DONE:
        task = state["task_id"]
        log(f"task {task} COMPLETE — archive: {rel(archived.parent)}/")
        notify(cfg, state, "done", f"task {task} done",
               (state["summaries"].get(frm) or {}).get("text") or "no summary given", task)
        state.update(status="idle", baton=None, task_id=None, loop=1)
        return
    if kind == "reject":
        if state["loop"] >= cfg["max_loops"]:
            halt(cfg, state, f"task {state['task_id']} hit max_loops ({cfg['max_loops']}) — "
                        f"see {rel(archived)}")
            return
        state["loop"] += 1
    deliver(cfg, state, target, archived, meta, kind)


def medic_available():
    return (PACK_DIR / "agents" / f"{MEDIC}.prompt").exists()


def summon_medic(cfg, state, note):
    """Point the medic at the current mess. Caller holds the lock.

    The medic is outside the flow: it never takes the baton, and its repairs
    land as edited files in queue/pending/ — back through the normal gate.
    """
    if state["seen"].get(MEDIC) != state["task_id"]:
        send_line(MEDIC, "/clear")
        time.sleep(1.5)
        state["seen"][MEDIC] = state["task_id"]
    pending = ", ".join(p.name for p in sorted(PENDING.glob("*.md"))) or "none"
    msg = (f"You are summoned — the pack looks stuck. "
           f"status={state['status']}  task={state['task_id'] or '-'}  "
           f"baton={state['baton'] or '-'}  loop={state['loop']}/{cfg['max_loops']}  "
           f"pending: {pending}. Diagnose and repair per your instructions, then "
           f"report in this window what you found and did.")
    if note:
        msg += f" Note from the human: {note}"
    send_line(MEDIC, msg)
    log(f"medic summoned  (status: {state['status']}, baton: {state['baton'] or '-'})")


# ---------------------------------------------------------------- TUI

class Cooked:
    """Temporarily restore normal terminal mode (for $EDITOR / input())."""

    def __init__(self, saved):
        self.saved = saved

    def __enter__(self):
        termios.tcsetattr(sys.stdin.fileno(), termios.TCSADRAIN, self.saved)

    def __exit__(self, *exc):
        tty.setcbreak(sys.stdin.fileno())


class TUI:
    def __init__(self):
        self.cfg = load_config()

    def run(self):
        fd = sys.stdin.fileno()
        self.saved = termios.tcgetattr(fd)
        tty.setcbreak(fd)
        try:
            while True:
                self.tick()
                self.render()
                if select.select([sys.stdin], [], [], 1.0)[0]:
                    if not self.handle(sys.stdin.read(1)):
                        break
        except KeyboardInterrupt:
            pass
        finally:
            termios.tcsetattr(fd, termios.TCSADRAIN, self.saved)

    @staticmethod
    def pending_files():
        PENDING.mkdir(parents=True, exist_ok=True)
        return sorted(PENDING.glob("*.md"), key=lambda p: p.stat().st_mtime)

    def routable(self):
        """Pending handoffs that have stopped changing. An agent may write its
        handoff in more than one step, and with the gate gone there is no human
        left to notice a half-written file before it is delivered."""
        now = time.time()
        return [p for p in self.pending_files() if now - p.stat().st_mtime >= SETTLE]

    def roster(self):
        names = list(self.cfg["agents"])
        if medic_available() and MEDIC not in names:
            names.append(MEDIC)
        return names

    @staticmethod
    def wrap(text, width, limit=2):
        """Fold one line of text onto at most `limit` lines of `width`."""
        lines, cur = [], ""
        for word in text.split():
            candidate = f"{cur} {word}".strip()
            if cur and len(candidate) > width:
                lines.append(cur)
                cur = word
                if len(lines) == limit:
                    break
            else:
                cur = candidate
        if cur and len(lines) < limit:
            lines.append(cur)
        if len(" ".join(lines)) < len(text) and lines:
            lines[-1] = lines[-1][:width - 1].rstrip() + "…"
        return lines

    def tick(self):
        """Route every handoff the moment it lands — there is no approval gate.

        Human tasks still wait for an idle pack, so tasks run one at a time,
        and a halt freezes routing until you clear it with [x]. Also the place
        the "an agent is blocked on a prompt" ping is fired from.
        """
        with Lock():
            state = load_state()
            for path in self.routable():
                if state["status"] == "halted":
                    break
                meta, body = parse_frontmatter(path.read_text())
                if not meta:
                    continue
                if meta.get("from") != HUMAN or state["status"] == "idle":
                    route(self.cfg, state, path, meta, body)
            for agent, at, msg in self.blocked_agents(state):
                notify(self.cfg, state, f"needs_input:{agent}", f"{agent} needs you",
                       msg, at)
            save_state(state)

    def handle(self, key):
        if key == "q":
            return False
        if key == "x":
            with Lock():
                state = load_state()
                if state["status"] == "halted":
                    state["notified"].pop("halted", None)  # re-arm the ping
                    # a repaired handoff may still be queued — pick it back up
                    # rather than dropping the task it belongs to
                    if any(not p.stem.endswith(f"-{HUMAN}") for p in self.pending_files()):
                        state.update(status="working", halt_reason=None)
                        log("halt cleared — retrying the queued handoff")
                    else:
                        state.update(status="idle", task_id=None, baton=None, loop=1,
                                     halt_reason=None)
                        log("halt cleared — pack idle")
                    save_state(state)
            return True
        if key == "m":
            if not medic_available():
                log("no agents/medic.prompt — cannot summon the medic")
                return True
            with Cooked(self.saved):
                try:
                    note = input("\n  note for the medic (optional)> ").strip()
                except EOFError:
                    note = ""
            try:
                with Lock():
                    state = load_state()
                    summon_medic(self.cfg, state, note)
                    save_state(state)
            except subprocess.CalledProcessError:
                # medic.prompt added after the session started — no window yet
                log("no medic window — pack down && pack up to launch it")
            return True
        return True

    def blocked_agents(self, state):
        """Agents sitting on a prompt that actually stalls the pack: the one
        holding the baton, and the medic, who works outside the flow."""
        out = []
        for agent in self.roster():
            if agent != state["baton"] and agent != MEDIC:
                continue
            note = waiting_note(agent)
            if note:
                out.append((agent, *note))
        return out

    def render(self):
        state = load_state()
        roster = self.roster()
        blocked = self.blocked_agents(state)
        queued = sum(1 for p in self.pending_files() if p.stem.endswith(f"-{HUMAN}"))
        out = ["\x1b[2J\x1b[H"]
        out.append(f"  PACK · {self.cfg['name']}"
                   f"{' ' * max(1, 40 - len(self.cfg['name']))}status: {state['status'].upper()}")
        out.append(f"  task: {state['task_id'] or '—'}   baton: {state['baton'] or '—'}   "
                   f"loop: {state['loop']}/{self.cfg['max_loops']}   queued tasks: {queued}")
        out.append("  " + "─" * 70)
        if blocked:
            for agent, at, msg in blocked:
                out.append(f"  ⏸ {agent.upper()} IS WAITING ON YOU  (since {at})")
                for line in self.wrap(msg, 66):
                    out.append(f"    {line}")
            out.append("    answer in that window — Ctrl-b n cycles through them")
        elif state["status"] == "halted":
            out.append("  ✖ HALTED — the pack will not route anything until you clear this")
            for line in self.wrap(state.get("halt_reason") or "see the log below", 66):
                out.append(f"    {line}")
        elif state["status"] == "working":
            out.append(f"  ▸ {state['baton']} is working on task {state['task_id']} …")
        else:
            out.append('  idle — file a task with:  pack/bin/pack run "…"')
        out.append("")
        out.append("  agents:")
        pad = max(len(a) for a in roster) if roster else 6
        waiting = {agent: msg for agent, _, msg in blocked}
        for agent in roster:
            last = state["summaries"].get(agent) or {}
            if agent in waiting:
                mark, text = "⏸ waiting", waiting[agent]
            elif agent == state["baton"] and state["status"] == "working":
                mark, text = "▸ working", "…"
            elif last:
                verdict = last.get("verdict", "done")
                mark = f"{'✗' if verdict == 'reject' else '✓'} {verdict}"
                text = f"{last.get('at', '')}  {last.get('text', '')}".strip()
            else:
                mark, text = "· idle", ""
            head = f"    {agent:<{pad}}  {mark[:10]:<10}  "
            lines = self.wrap(text, 74 - len(head)) or [""]
            out.append(head + lines[0])
            for line in lines[1:]:
                out.append(" " * len(head) + line)
        out.append("  " + "─" * 70)
        out.append("  recent:")
        for line in tail(LOG_FILE, 8):
            out.append("    " + line[:76])
        keys = "  [m] summon medic" if medic_available() else "  "
        if state["status"] == "halted":
            keys += "   [x] clear halt"
        out.append("")
        out.append(f"{keys}   [q] quit tui")
        sys.stdout.write("\n".join(out) + "\n")
        sys.stdout.flush()


# ---------------------------------------------------------------- commands

def load_agent(name):
    path = PACK_DIR / "agents" / f"{name}.prompt"
    if not path.exists():
        sys.exit(f"pack: agent '{name}' is in pack.yaml but {rel(path)} does not exist")
    meta, body = parse_frontmatter(path.read_text())
    return meta or {}, body.strip()


def agent_command(name, meta, body):
    argv = [
        "env", f"PACK_AGENT={name}", f"PACK_DIR={PACK_DIR}",
        "claude",
        "--settings", str(PACK_DIR / "settings.json"),
        "--append-system-prompt", body,
    ]
    if meta.get("model"):
        argv += ["--model", str(meta["model"])]
    if meta.get("effort"):
        argv += ["--effort", str(meta["effort"])]
    tools = meta.get("tools") or {}
    if tools.get("allow"):
        argv += ["--allowedTools", ",".join(tools["allow"])]
    if tools.get("deny"):
        argv += ["--disallowedTools", ",".join(tools["deny"])]
    return " ".join(shlex.quote(a) for a in argv)


def cmd_up():
    for tool in ("tmux", "claude"):
        if not shutil.which(tool):
            sys.exit(f"pack: '{tool}' not found — run `pack/bin/pack setup` first")
    cfg = load_config()
    if session_exists():
        sys.exit(f"pack: session '{SESSION}' already running — pack attach, or pack down first")
    names = list(cfg["agents"])
    if medic_available() and MEDIC not in names:
        names.append(MEDIC)
    agents = [(name, *load_agent(name)) for name in names]
    PENDING.mkdir(parents=True, exist_ok=True)
    RUNS.mkdir(parents=True, exist_ok=True)
    me = str(Path(__file__).resolve())
    tmux("new-session", "-d", "-s", SESSION, "-n", "dispatcher", "-c", str(PROJECT_DIR),
         f"env PACK_DIR={shlex.quote(str(PACK_DIR))} python3 {shlex.quote(me)} tui")
    # keep windows visible if a command inside them dies, instead of vanishing
    tmux("set-option", "-t", SESSION, "remain-on-exit", "on", check=False)
    if cfg["notify"] == "desktop":
        # Let the OSC 9/777 notifications out of the pane up to the terminal.
        # allow-passthrough is a *pane* option with no per-session scope, so this
        # must be global: targeting the session (-t) stores it where pane lookups
        # never consult, and it silently does nothing. "all" rather than "on"
        # because "on" only forwards from the visible pane — and the pings that
        # matter fire from the dispatcher while you are watching an agent window.
        tmux("set-option", "-g", "allow-passthrough", "all", check=False)
    for name, meta, body in agents:
        tmux("new-window", "-t", SESSION, "-n", name, "-c", str(PROJECT_DIR),
             agent_command(name, meta, body))
    tmux("select-window", "-t", f"{SESSION}:dispatcher")
    log(f"pack up — agents: {', '.join(names)}")
    print(f"pack is up: dispatcher + {', '.join(names)}")
    print('  pack/bin/pack run "<task>"    file a task')
    print("  pack/bin/pack attach          watch (Ctrl-b n: next window, Ctrl-b d: detach)")


def cmd_submit(text):
    text = text.strip()
    if not text:
        sys.exit('pack: empty task — usage: pack/bin/pack run "do the thing"')
    with Lock():
        state = load_state()
        state["task_seq"] += 1
        task_id = f"{state['task_seq']:03d}"
        PENDING.mkdir(parents=True, exist_ok=True)
        (PENDING / f"{task_id}-{HUMAN}.md").write_text(
            "---\n"
            f'task_id: "{task_id}"\n'
            f"from: {HUMAN}\n"
            "verdict: done\n"
            f"created: {datetime.now().isoformat(timespec='seconds')}\n"
            "---\n\n"
            "## Task\n\n"
            f"{text}\n"
        )
        save_state(state)
    log(f"task {task_id} filed: {text[:60]}")
    print(f"task {task_id} filed")
    if not session_exists():
        print("note: the pack is not running — start it with pack/bin/pack up")


def cmd_status():
    cfg = load_config()
    state = load_state()
    print(f"pack: {cfg['name']}   session: {'running' if session_exists() else 'not running'}")
    print(f"status: {state['status']}   task: {state['task_id'] or '—'}   "
          f"baton: {state['baton'] or '—'}   loop: {state['loop']}/{cfg['max_loops']}")
    if state["status"] == "halted" and state.get("halt_reason"):
        print(f"halted: {state['halt_reason']}")
    print("agents:")
    for agent in cfg["agents"] + ([MEDIC] if medic_available() else []):
        note = waiting_note(agent)
        last = state["summaries"].get(agent) or {}
        if note:
            print(f"  {agent}: WAITING — {note[1]}")
        elif last:
            print(f"  {agent}: {last.get('verdict')} {last.get('at')} — {last.get('text')}")
        else:
            print(f"  {agent}: idle")
    pending = sorted(PENDING.glob("*.md")) if PENDING.exists() else []
    print(f"pending handoffs: {len(pending)}")
    for p in pending:
        print(f"  - {p.name}")
    recent = tail(LOG_FILE, 8)
    if recent:
        print("recent:")
        for line in recent:
            print(f"  {line}")


def cmd_reset():
    with Lock():
        for p in list(PENDING.glob("*.md")) if PENDING.exists() else []:
            p.unlink()
        for p in list(QUEUE.glob(".attempts_*")) + list(QUEUE.glob(".waiting_*")):
            p.unlink()
        # keep task numbering monotonic so a fresh task never collides with
        # an old runs/<task>/ archive
        task_seq = load_state()["task_seq"]
        state = dict(DEFAULT_STATE)
        state["task_seq"] = task_seq
        save_state(state)
    log("reset — pending queue and state cleared")
    print("pack reset: pending queue + state cleared (runs/ archive kept)")


def main():
    cmd = sys.argv[1] if len(sys.argv) > 1 else "status"
    if cmd == "up":
        cmd_up()
    elif cmd == "tui":
        TUI().run()
    elif cmd == "submit":
        cmd_submit(" ".join(sys.argv[2:]))
    elif cmd == "status":
        cmd_status()
    elif cmd == "reset":
        cmd_reset()
    else:
        sys.exit(__doc__)


if __name__ == "__main__":
    main()
