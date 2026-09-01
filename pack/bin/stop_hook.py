#!/usr/bin/env python3
"""Pack Stop hook — an agent that holds the baton may not go idle without
having written its handoff file. Registered via pack/settings.json; relies on
PACK_AGENT and PACK_DIR being set in the agent's environment (pack up does this).

Exit 2 blocks the stop and feeds stderr back to the agent as instructions.
After MAX_NAGS blocked attempts we let the agent stop and log the stall, so a
confused agent cannot burn tokens forever.
"""

import json
import os
import sys
from datetime import datetime
from pathlib import Path

MAX_NAGS = 3


def main():
    agent = os.environ.get("PACK_AGENT")
    pack = os.environ.get("PACK_DIR")
    if not agent or not pack:
        return 0
    pack = Path(pack)
    try:
        sys.stdin.read()  # hook payload unused; consume so claude isn't blocked
    except OSError:
        pass
    (pack / "queue" / f".waiting_{agent}").unlink(missing_ok=True)  # turn over, not blocked

    state_file = pack / "queue" / "state.json"
    if not state_file.exists():
        return 0
    try:
        state = json.loads(state_file.read_text())
    except (OSError, ValueError):
        return 0
    if state.get("baton") != agent or state.get("status") != "working":
        return 0

    pending = pack / "queue" / "pending"
    if pending.exists() and list(pending.glob(f"*-{agent}.md")):
        return 0  # handoff written — free to stop

    nag_file = pack / "queue" / f".attempts_{agent}"
    nags = 0
    if nag_file.exists():
        try:
            nags = int(nag_file.read_text().strip() or 0)
        except ValueError:
            nags = 0
    if nags >= MAX_NAGS:
        nag_file.unlink(missing_ok=True)
        with open(pack / "queue" / "log.txt", "a") as fh:
            fh.write(f"{datetime.now().strftime('%H:%M:%S')}  "
                     f"STALLED — {agent} stopped {MAX_NAGS}x without a handoff\n")
        return 0
    nag_file.write_text(str(nags + 1))

    task = state.get("task_id") or "current"
    print(
        f"You are pack agent '{agent}' and hold the baton for task {task}, but no "
        f"handoff file exists. Before stopping you MUST write your handoff to "
        f"{pack.name}/queue/pending/{task}-{agent}.md following {pack.name}/HANDOFF.md. "
        "If you are blocked, write the handoff anyway and explain the blocker in Summary.",
        file=sys.stderr,
    )
    return 2


if __name__ == "__main__":
    sys.exit(main())
