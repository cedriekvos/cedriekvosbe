#!/usr/bin/env python3
"""Pack Notification hook — records that an agent is blocked on a prompt.

Claude fires Notification when it wants permission for a tool, or when it has
been sitting on an unanswered prompt. We drop the message in
queue/.waiting_<agent> so the dispatcher overview can surface it; it is removed
again the moment anything is typed into that window (dispatcher send_line), the
agent's next tool call returns (PostToolUse), or its turn ends (stop_hook).

Registered via pack/settings.json; relies on PACK_AGENT and PACK_DIR being set
in the agent's environment (pack up does this).
"""

import json
import os
import sys
from datetime import datetime
from pathlib import Path

MSG_MAX = 200


def main():
    agent = os.environ.get("PACK_AGENT")
    pack = os.environ.get("PACK_DIR")
    if not agent or not pack:
        return 0
    try:
        payload = json.loads(sys.stdin.read() or "{}")
    except (OSError, ValueError):
        payload = {}
    message = " ".join(str(payload.get("message") or "waiting for your input").split())
    queue = Path(pack) / "queue"
    try:
        queue.mkdir(parents=True, exist_ok=True)
        (queue / f".waiting_{agent}").write_text(
            f"{datetime.now().strftime('%H:%M:%S')}\t{message[:MSG_MAX]}\n"
        )
    except OSError:
        pass  # a missing marker only costs a line on the overview
    return 0


if __name__ == "__main__":
    sys.exit(main())
