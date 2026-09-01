# Agent Pack

`pack/` is a self-contained multi-agent pipeline, separate from the Laravel
app itself, that can drive a feature from spec to reviewed implementation
autonomously in tmux. Prompt-defined agents under `pack/agents/*.prompt` hand
work to each other through `pack/queue/` (the message bus); a dispatcher
(`pack/bin/dispatcher.py`) routes each handoff per the flow declared in
`pack/pack.yaml` and archives the full audit trail per task under
`pack/runs/`. Full usage docs live in `pack/README.md`.

This project's flow (`pack/pack.yaml`): `feature-specifier` (Gherkin spec +
Dutch `leesmij`) → optionally `feature-architect` (an ADR, only for a
genuine cross-cutting technical decision) → `feature-to-pest` (red Pest
feature tests) → `feature-development` (`app/` code, test-first, until
`composer qa` is green) → `feature-reviewer` (independent, read-only review)
→ done, or back to whichever stage can actually fix what's wrong.

- Start a run with `pack/bin/pack run "<task description>"`; watch it with
  `pack/bin/pack attach`.
- Every agent's handoff must follow the schema in `pack/HANDOFF.md` — read it
  before writing or reviewing an agent prompt.
- `pack/agents/medic.prompt` is an on-call repair agent (summoned with `[m]`
  in the dispatcher) that fixes the pack's own plumbing (queue/state) when it
  jams — never project code.
- When editing an `agents/*.prompt` file: keep its `verdict:` values in sync
  with the edges `pack.yaml` defines for that agent, and its `tools.allow`/
  `deny` in sync with what the prompt actually asks the agent to do — a tool
  that's neither allowed nor denied falls back to an interactive permission
  prompt, which stalls an agent running unattended in its tmux window.
- `pack/queue/` and `pack/runs/` are gitignored runtime state, not source —
  don't hand-edit them outside the medic's own playbook.
