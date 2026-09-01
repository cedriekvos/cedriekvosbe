# Handoff protocol

Every agent ends its turn by writing exactly one handoff file. The dispatcher
routes it to the next agent based on `pack.yaml`, on its own, within a second
or two of you writing it. Nobody approves it first — write it as if it ships,
because it does.

## Where

`pack/queue/pending/<task_id>-<your-agent-name>.md` — e.g. `003-coder.md`.

The delivery message you received names the exact path; use that.

## Format

```markdown
---
task_id: "003"        # quote it — keeps the leading zeros
from: coder           # your agent name
verdict: done         # coder: done · qa: approve | reject
summary: >-           # ONE sentence, max ~110 chars — this is what the human
  Added retry-on-IOError to save() and a test for the empty-list case.
files_changed:        # optional but appreciated
  - src/app.py
  - tests/test_app.py
---

## Summary

What you did and why, in a few sentences.

## Context for the next agent

Everything the next agent needs, assuming ZERO prior context: what the task
was, key decisions you made, how to verify the work, known gotchas.

## Remarks

(only for qa `reject`) A checklist of specific, actionable problems:
- [ ] `save()` swallows the IOError — re-raise or log it
- [ ] no test covers the empty-list case
```

## Verdicts

**`pack.yaml`'s `flow.edges.<your-agent-name>` is the only authoritative list
of verdicts you may use.** Look yourself up there before writing your handoff
— do not guess, and do not copy a verdict name from another agent's handoff
or from the example below. A verdict with no matching edge is not rejected
loudly: the dispatcher just leaves your handoff sitting in the queue, which
looks like the pipeline hung.

The table below is illustrative only, for a generic `coder`/`qa` pack — this
repo's actual agents and verdicts (e.g. `feature-specifier: ready |
needs_decision`) are almost certainly different. Check `pack.yaml`.

| agent | verdict   | meaning                                        |
|-------|-----------|------------------------------------------------|
| coder | `done`    | work complete, ready for review                |
| qa    | `approve` | ship it — the task is finished                 |
| qa    | `reject`  | remarks must be addressed; bounces back        |

## Rules

- Write `summary:` on every handoff. The human watching the dispatcher sees
  only that line, not your Summary section — so make it say what changed, not
  "task complete". One sentence, no markdown, no file list.
- One handoff per turn, and write it in a single pass: the dispatcher collects
  it about a second after your last write. If you must revise it, overwrite
  your own pending file — but assume it has already gone out.
- Never edit `pack/queue/state.json`, other agents' pending files, or anything
  under `pack/runs/`.
- Blocked? Write the handoff anyway and explain the blocker in **Summary**.
  Never just stop — the Stop hook will not let you end your turn without a
  handoff file.
