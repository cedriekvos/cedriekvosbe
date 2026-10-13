---
name: feature-architect
description: "Use this skill whenever a change raises a cross-cutting technical decision that must hold across features — the optional step 2 of this project's full pipeline, and equally on the light track. Trigger before adding a composer or npm dependency, choosing a file or serialization format, deciding how a new bounded domain is layered and stored, adding a directory under app/ or documentation/, or picking caching, queue, storage-disk or scheduled-command infrastructure — and whenever the spec, the acceptance tests or the implementation turn out to depend on such a choice nobody has recorded. Covers: what warrants an ADR and what does not, framing the question, researching and weighing alternatives, when to ask the user, and Proposed versus Accepted. Does NOT cover the ADR page format (adr-authoring), externally visible behavior (feature-specifier) or the per-feature class graph (feature-development)."
metadata:
  author: cedriekvos
---

# Feature Architect

You are making a **technical decision that must hold across features** — the
choices between the behavior spec and the per-feature code. This step produces
**decisions only**: an Architecture Decision Record (ADR) under
`documentation/decisions/`. No production code, no tests, no spec changes, and
nothing to run.

This step is **conditional** — it applies only when a change raises a genuine
cross-cutting question. Don't manufacture an ADR where none is warranted; if
the driver turns out not to need one, say so and carry on with no new file.

## What belongs here (and what doesn't)

Decide here:
- **New dependencies** (composer/npm) — runtime or dev.
- **File & serialization formats** and the library behind them.
- **A new bounded domain's layering & storage strategy** — how it maps onto the
  `app/Blog` pattern (raw storage → format → source/factory → read/write repos
  → facade) or deliberately diverges, and why.
- **Directory-structure additions** under `app/` or `documentation/`.
- **Cross-cutting infrastructure** — caching, queue/connection choice, storage
  disks, scheduled-command boundaries.

Defer, don't duplicate:
- Externally visible behavior → the **feature-specifier** skill.
- Per-feature class graph → the **feature-development** skill, decided
  in-context; you set the strategy here, the implementation designs within it.
- Generic, already-settled Laravel conventions → the **laravel-best-practices**
  skill/convention; record an ADR only for a genuine *choice*.

## Workflow

1. **Read the driver.** Read the `.feature` spec, its Dutch `leesmij`, any
   existing ADRs under `documentation/decisions/`, and the relevant `app/`
   code, so you don't contradict or re-decide something already settled.
2. **Frame the question** precisely. If it turns out to be behavior,
   per-feature design, or settled convention, say so and move on — no ADR.
3. **Research** with `search-docs` for version-specific guidance; weigh at
   least the leading two real alternatives against this project's constraints
   and house style.
4. **Surface genuine forks** with **AskUserQuestion** and a recommended option
   — always for a new dependency. Never add a dependency without explicit
   approval.
5. **Record the ADR** — invoke the `adr-authoring` skill and follow it.
6. **Carry the decision forward**: name the ADR and the constraints it puts on
   the acceptance tests and the implementation.

## Recording the ADR

**Invoke the `adr-authoring` skill** before you write anything into
`documentation/decisions/`. It owns the ADR file format — HTML pages started
from `documentation/decisions/template.html`, the four sections, the writing
rules that keep a page short enough to be reread, the index row and prev/next
wiring, and how superseding works. Never hand-write an ADR from memory: the
format has changed before, and the skill is the only current source for it.

What stays with this skill, not that one:

- Whether an ADR is warranted at all — if none is, say so and move on.
- Which option wins, and which rejected alternatives are worth recording.
- `Proposed` vs `Accepted` — `Accepted` only once the user has approved any
  dependency or irreversible choice the decision implies.
- The driver and related ADRs the page should point at.

## Boundaries — escalate instead of guessing

- Decisions only, under `documentation/decisions/` and nowhere else.
- No dependency marked `Accepted` before the user agrees.
- If the real question is behavior rather than architecture, don't answer it
  in an ADR — it goes back to the spec (**feature-specifier**). State the
  behavioral question, and take that route only when the spec genuinely cannot
  be implemented as written, not for a wording preference.
- When the driver turned out not to need an ADR, say plainly that none was
  needed and why, so nothing downstream waits on one.
