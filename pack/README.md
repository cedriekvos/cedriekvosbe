# pack

A wolfpack of Claude Code agents for your project: agents defined in `.prompt`
files hand work off to each other through the filesystem and route themselves,
while you watch the whole hunt from one screen. Pack is **a directory, not an
environment** — it ships no Dockerfile and no compose file. You bring your own
container (Laravel Sail, the official Python image, anything vaguely Linux);
pack installs itself into it.

## How it works

```
┌─ your container ───────────────────────────────────────────────┐
│ tmux session "pack"                                            │
│ ┌────────────┐  ┌─────────┐  ┌─────────┐  ┌─────────┐          │
│ │ dispatcher │  │  coder  │  │   qa    │  │  medic  │ ← one    │
│ │ (overview) │  │ claude  │  │ claude  │  │ on call │ window   │
│ └─────┬──────┘  └────┬────┘  └────┬────┘  └────┬────┘ each     │
│       │              │ writes     │ writes     │ repairs       │
│       │              ▼            ▼            ▼               │
│       │         pack/queue/pending/*.md   ← handoffs           │
│       │                                                        │
│       └── routes on sight → tmux send-keys delivers ──────────►│
│                routed handoffs archive to pack/runs/<task>/    │
└────────────────────────────────────────────────────────────────┘
```

- **The filesystem is the message bus.** An agent finishes by writing a
  handoff file (frontmatter + markdown, see [HANDOFF.md](HANDOFF.md)) into
  `queue/pending/`. tmux is only the delivery mechanism and your viewport.
- **Handoffs route themselves.** A handoff that lands in `queue/pending/` is
  archived to `runs/<task>/` — the full, ordered audit trail of every task —
  and delivered to the next agent about a second later. You are not in the
  loop; the pack only stops for you when it halts or an agent needs an answer.
- **One screen tells you everything.** The dispatcher window is a live
  overview: who holds the baton, what each agent last reported doing (its
  handoff's one-line `summary:`), and — in a banner you cannot miss — which
  agent is sitting on a permission prompt waiting for you, and since when.
- **It tells you when it needs you.** An agent stuck on a prompt, a finished
  task and a halt each ping once — a terminal bell by default, a real desktop
  notification or a push to your phone if you point `notify:` at one. See
  [Notifications](#notifications).
- **Agents can't ghost.** A Stop hook ([bin/stop_hook.py](bin/stop_hook.py))
  blocks the agent holding the baton from going idle until its handoff exists
  (it gives up after 3 attempts and logs the stall instead of burning tokens).
- **Task-scoped memory.** An agent gets `/clear` before each *new* task, but
  keeps its context through QA-rejection loops on the same task — the coder
  remembers why it did what it did when the remarks come back.
- **Bounded loops.** After `max_loops` rejections on one task the pack halts
  and waits for you. So does a verdict with no edge in `pack.yaml` — with
  nobody at a gate, an unroutable handoff stops the pack instead of sitting
  unnoticed in the queue.
- **A medic on call.** When the pack jams — an invalid verdict, a stalled
  agent, a mangled handoff — press **m** in the dispatcher: the medic (its own
  claude window, outside the flow) reads the queue, the log, and the agent
  panes, then repairs the plumbing or tells you what's wrong. It never takes
  the baton, and never routes anything itself — it edits the queue and lets
  the dispatcher pick the repair up. Delete `agents/medic.prompt` to opt out.

## Layout

```
pack/
├── pack.yaml          # roster + flow: who hands off to whom, loop limits
├── agents/
│   ├── coder.prompt   # frontmatter (tools, model, effort) + system prompt
│   ├── qa.prompt      # read-only reviewer — may only write its handoff
│   └── medic.prompt   # on-call repair agent — press m when the pack jams
├── bin/
│   ├── pack           # the CLI (below)
│   ├── dispatcher.py  # queue router, overview TUI, tmux orchestration
│   ├── stop_hook.py   # "no idling without a handoff" enforcement
│   └── notify_hook.py # records which agent is waiting on a prompt
├── settings.json      # claude settings the agents launch with (the hooks)
├── HANDOFF.md         # the handoff schema — agents are pointed at this
├── queue/             # runtime: handoffs, state, waiting flags (gitignored)
└── runs/              # archive: one dir per task             (gitignored)
```

Pack never touches your project's `.claude/` — agents launch with
`--settings pack/settings.json`, so everything stays inside this directory.

## Getting started

All commands run **inside your project's container**, from the project root
(the directory containing `pack/`).

```sh
# 0. get a shell in your container, e.g.:
#      ./vendor/bin/sail shell            (Laravel Sail)
#      docker compose exec app bash       (generic)

# 1. one-time per container: install/check tmux, python3+PyYAML, claude, auth
pack/bin/pack setup

# 2. start the pack (tmux session: dispatcher + one window per agent)
pack/bin/pack up

# 3. give the wolves something to hunt
pack/bin/pack run "Add validation to the signup endpoint and cover it with tests"

# 4. watch the hunt
pack/bin/pack attach
```

Inside the session: `Ctrl-b n` cycles windows (dispatcher → coder → qa →
medic), `Ctrl-b d` detaches. The dispatcher window is the overview and needs
no input to keep the pack moving; its keys are **m** summon the medic, **x**
clear a halt, **q** quit the TUI.

The two things it will ask of you: a **⏸ WAITING ON YOU** banner when an agent
hits a permission prompt (answer it in that agent's window — `Ctrl-b n`), and
a **✖ HALTED** banner when the pack gives up on its own. Everything else it
just reports: each agent's row shows the one-line summary of what it last did.

```
  PACK · myproject                        status: WORKING
  task: 004   baton: qa   loop: 1/3   queued tasks: 0
  ──────────────────────────────────────────────────────────────────────
  ⏸ QA IS WAITING ON YOU  (since 14:07:12)
    Claude needs your permission to use Bash
    answer in that window — Ctrl-b n cycles through them

  agents:
    coder  ✓ done      14:06  Added retry-on-IOError to save() and covered
                              the empty-list case with a test.
    qa     ⏸ waiting   Claude needs your permission to use Bash
    medic  · idle
  ──────────────────────────────────────────────────────────────────────
  recent:
    14:06:41  handoff → qa  (004-01-coder.md)

  [m] summon medic   [q] quit tui
```

## Commands

| command | what it does |
|---|---|
| `pack/bin/pack setup` | install/verify tmux, python3, PyYAML, claude; check auth |
| `pack/bin/pack up` | start the tmux session (dispatcher + agent windows) |
| `pack/bin/pack run "<task>"` | file a task (queued if the pack is busy) |
| `pack/bin/pack attach` | attach to the session |
| `pack/bin/pack status` | one-shot summary: state, pending handoffs, recent log |
| `pack/bin/pack reset` | clear pending queue + state; keeps the `runs/` archive |
| `pack/bin/pack down` | kill the tmux session |
| `pack/bin/pack version` | print pack version |

## Notifications

The pack pings you on three events: an agent blocked on a prompt, a task
finished, and a halt. Each fires once — a new prompt pings again, the same one
does not. Pick how in `pack.yaml`:

```yaml
pack:
  notify: desktop
```

| value | what happens |
|---|---|
| `bell` (default) | rings the terminal — your terminal or tmux decides what that means |
| `desktop` | the bell, plus OSC 9 **and** OSC 777 escapes — iTerm2, WezTerm and Windows Terminal read the first, foot and urxvt the second, and each ignores the other (`pack up` enables tmux's `allow-passthrough` so they get out of the pane) |
| any other string | run as a shell command |

`desktop` only reaches you **while you are attached** — the escapes travel down
the tty, so a detached session has nowhere to send it. To be told about a pack
you are not watching, use a command instead. It runs with `PACK_EVENT`
(`needs_input`, `done` or `halted`), `PACK_NAME`, `PACK_TITLE`, `PACK_BODY` and
`PACK_TASK` in its environment, detached from the dispatcher — anything it
prints is discarded, and a failure is logged, never fatal:

```yaml
  # phone, via ntfy.sh — no account, works from inside the container
  notify: 'curl -s -d "$PACK_TITLE: $PACK_BODY" ntfy.sh/my-pack-topic'

  # Slack
  notify: 'curl -s -X POST -H "Content-type: application/json"
           -d "{\"text\":\"$PACK_NAME — $PACK_TITLE: $PACK_BODY\"}" "$SLACK_WEBHOOK"'

  # a Linux host, if the container can reach its session bus
  notify: 'notify-send "$PACK_NAME: $PACK_TITLE" "$PACK_BODY"'

  # only bother me when something is actually stuck
  notify: '[ "$PACK_EVENT" = done ] || curl -s -d "$PACK_TITLE" ntfy.sh/my-pack-topic'
```

Remember the command runs **inside your container**, so whatever it calls has
to be installed there and able to reach the outside — which is why a push
service usually beats trying to talk to the host desktop.

Most terminals also refuse to notify for a window that already has focus —
foot's `inhibit-when-focused` defaults to `yes`. Because every agent lives in a
tmux window inside *one* terminal window, that means an attached pack will not
notify you while you are looking at any of its windows. Set
`inhibit-when-focused=no` in `foot.ini` if you want the ping regardless.

To check your terminal understands either escape before wiring it up, run this
where the pack would run — inside the container, inside tmux — then click away
so the window loses focus:

```sh
tmux set -g allow-passthrough on
sleep 3
printf '\033Ptmux;\033\033]9;pack test\007\033\\'                      # OSC 9
printf '\033Ptmux;\033\033]777;notify;pack;test\007\033\\'             # OSC 777
```

On Wayland this needs a notification daemon running (mako, dunst, swaync) —
`notify-send hello` on the host is the quickest way to rule that out first.

## Configuring the flow

`pack.yaml` defines the pack. Verdicts in an agent's handoff select the edge;
`DONE` completes the task:

```yaml
flow:
  entry: coder
  edges:
    coder:
      done: qa
    qa:
      approve: DONE
      reject: coder
```

Add an agent by dropping `agents/<name>.prompt` and wiring it into `edges`.
For example a planner in front: `entry: planner` and `planner: { done: coder }`.

The medic is the one agent that does *not* go in `edges`: if
`agents/medic.prompt` exists, `pack up` launches it on call, and **m** in the
dispatcher summons it. It has no verdicts and never holds the baton.

## Writing agents (`.prompt` files)

YAML frontmatter + markdown system prompt:

```markdown
---
name: coder
description: Implements tasks in this codebase
model: sonnet                    # optional; omit to use the default
effort: high                     # optional; low|medium|high|xhigh|max
tools:
  allow: [Read, Edit, Write, "Bash(git diff:*)"]
  deny:  [WebSearch]
---
You are **coder** … (becomes the agent's --append-system-prompt)
```

`model` and `effort` map to `--model`/`--effort`; `tools.allow`/`tools.deny` map
to `--allowedTools`/`--disallowedTools`. Tools that are neither allowed nor
denied fall back to normal permission prompts — attach to the window to answer
them. Give the coder your project's test runner (e.g. `"Bash(php artisan test:*)"`,
`"Bash(pytest:*)"`) so it can verify its own work.

## Container requirements & auth

Your image needs: `bash`, `git`, and installability of `tmux` + `python3` +
PyYAML (`pack setup` tries apk/apt/dnf, and installs `claude` via the native
installer). On Alpine, baking it in is one line:

```dockerfile
RUN apk add --no-cache tmux python3 py3-yaml
```

Auth: either set `ANTHROPIC_API_KEY`, or run `claude` once to log in — and
persist the login across container restarts by mounting a named volume over
`$HOME` (or `$HOME/.claude`). `pack setup` tells you which it found.

## Copying pack to another project

```sh
cp -r pack/ /path/to/other-project/
```

Then edit `pack.yaml` (name, flow) and the `agents/*.prompt` tool lists for
that project's stack, and run `pack/bin/pack setup` inside its container.
`queue/` and `runs/` are gitignored runtime state; [version](version) and
[CHANGELOG.md](CHANGELOG.md) travel with the copy so you know what you have.

## Troubleshooting

- **A window vanished / shows a dead pane** — `remain-on-exit` keeps crashed
  windows visible; read the error, fix, `pack down && pack up`.
- **An agent stopped without handing off** — after 3 blocked attempts the
  Stop hook lets it stop and logs `STALLED` (visible in the dispatcher).
  Press **m** to send the medic, attach to the agent's window and talk to it,
  or `pack reset`.
- **HALTED in the dispatcher** — either `max_loops` rejections on one task, or
  a verdict with no edge in `pack.yaml`. The banner says which. For a bad
  verdict, press **m** and the medic will match it to one of the sender's
  valid verdicts; for a loop limit, review `runs/<task>/` and re-scope. Press
  **x** to clear the halt — a repaired handoff still in the queue is retried.
- **No notifications** — `notify: desktop` only works while attached, and only
  in terminals that understand OSC 9. For a detached or remote pack use a
  command (see [Notifications](#notifications)); test it by hand first, in the
  container, with `PACK_TITLE` and `PACK_BODY` set.
- **State looks wrong** — `pack reset` clears `queue/`; the `runs/` archive
  is never touched.
