# Changelog

## 5 — 2026-09-09

The pack stops handing the same work to two agents.

- **A handoff is never delivered twice.** The Stop hook used to look for the
  handoff only in `queue/pending/`, but the dispatcher moves it to
  `runs/<task>/` the moment it routes it — so an agent that stopped in that
  window was told its handoff was missing, wrote it again, and the next agent
  got the same work twice. A handoff now counts as filed whether it is still
  queued or has just been archived for this round
- **`HANDOFF.md` drops the "overwrite your own pending file" escape hatch.**
  One handoff per turn, written last, never rewritten — a rewrite is a second
  delivery. A blocker found afterwards goes in the agent's window, for you to
  decide on
- **Every delivery message carries `loop <n>/<max>`**, not just rejections, so
  an agent can see when a `reject` would halt the pack rather than come back
  round to it

## 4 — 2026-08-18

The pack routes itself. The dispatcher is a screen you watch, not a gate you
operate.

- **The approval gate is gone.** A handoff is archived and delivered to the
  next agent about a second after it lands. `[a]` approve, `[e]` edit and
  `[r]` reject went with it; `[m]` summon medic, `[x]` clear halt and `[q]`
  quit remain
- **The dispatcher window is an overview**: a row per agent showing what it
  last reported doing, plus a banner naming any agent stuck on a permission
  prompt and how long it has been waiting there
- **Handoffs carry `summary:`** — one sentence, and the only part of an agent's
  work visible on that overview. Falls back to the opening paragraph of the
  `## Summary` section when an agent omits it. **Custom agent prompts should be
  told to write it**
- **The pack pings you** when an agent needs input, a task finishes, or it
  halts. `notify:` in `pack.yaml` chooses: `bell` (default), `desktop` for OSC
  9 / OSC 777 desktop notifications, or any shell command — which is how you
  reach a phone, with `PACK_EVENT`, `PACK_TITLE` and `PACK_BODY` in its
  environment
- **A verdict with no edge in `pack.yaml` now halts the pack**, reason on
  screen, instead of sitting unread in a queue nobody is gating any more.
  `[x]` clears the halt and retries the repaired handoff
- **Agents can pin reasoning effort**: `effort: xhigh` in a `.prompt`
  frontmatter maps to `--effort`, the way `model:` already maps to `--model`
- A handoff must sit untouched for 1.5s before it is routed, so an agent that
  writes its file in two passes never gets its half-written first pass
  delivered

## 3 — 2026-07-20

An on-call medic agent unsticks the pack.

- New `medic` agent: press `m` in the dispatcher TUI to summon a repair agent
  that reads the queue, `state.json`, the log, and the agent panes, then fixes
  the pack's plumbing — invalid verdicts, stalled handoffs, malformed
  frontmatter — instead of you doing it by hand
- The medic sits outside `pack.yaml`'s flow: it never holds the baton and never
  routes or approves anything, so its repairs surface back through your normal
  approval gate like any other handoff
- `pack up` launches a medic window whenever `agents/medic.prompt` exists;
  delete that file to opt out, and every `[m]` hint disappears from the TUI
- The `INVALID VERDICT` banner now offers `[m] send the medic` alongside
  `[e] fix the verdict`

## 2 — 2026-07-13

Invalid handoff verdicts no longer stall the pack silently.

- The dispatcher TUI now shows an `INVALID VERDICT` banner — including the
  agent's valid verdicts from `pack.yaml` — when a gated handoff's verdict has
  no matching edge, instead of presenting it like a normal approval whose
  `[a]` silently no-ops
- `HANDOFF.md` now declares `pack.yaml`'s `flow.edges` as the only
  authoritative verdict list and marks its `coder`/`qa` table as illustrative,
  so custom agents stop copying `done` literally instead of using their own
  verdicts

## 1 — 2026-07-13

Initial release.

- `coder → qa` default flow with human-gated handoffs (approve / edit / reject)
- tmux runtime: approval TUI in a `dispatcher` window + one window per agent
- Filesystem message bus: `queue/pending/` for handoffs awaiting the gate, `runs/<task>/` as the per-task audit trail
- Stop hook that blocks an agent from going idle while it holds the baton without writing its handoff
- Task-scoped agent memory: `/clear` on new tasks, context kept through QA-rejection loops
- Per-agent tool allowlists via `.prompt` frontmatter (QA cannot Edit/Write code)
- `pack setup` installs/verifies dependencies inside any container you bring (Alpine/Debian/Fedora-ish)
