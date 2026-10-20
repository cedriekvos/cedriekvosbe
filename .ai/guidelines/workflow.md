# How to Work in This Project

You do the work in this session, start to finish. How much process a request
gets depends on what it changes, so pick the track before you start.

## Pick the track

- **Full pipeline** — the request adds or changes behavior someone can see: a
  page, a screen, a command's output, a validation rule. A fix counts when it
  changes what a `.feature` file specifies.
- **Light track** — everything else: refactors, fixes that make the code match
  its existing spec, dependency bumps, tooling and configuration. Make the
  change with the tests it needs and get `composer qa:fast` green — no spec, no
  `leesmij`, no review round. Production code still follows the
  `feature-development` skill's house style.

When it is not obvious which applies, say which you picked and why before you
start.

## The full pipeline

Work through the steps in order and invoke the named skill at each one — it
carries that step's conventions. The spec and `leesmij` are part of the work,
not optional documentation: this guideline is the standing request for them.

1. **Spec** — `feature-specifier` skill. A Gherkin
   `documentation/features/{domain}/{feature}.feature` plus a Dutch
   `documentation/leesmij/{domain}/{feature}.md`.
2. **Decision, only when needed** — `feature-architect` skill, when the change
   needs a new dependency, a file or serialization format, storage or layering
   for a new bounded domain, a new directory under `app/` or `documentation/`,
   or cross-cutting infrastructure. It records an ADR under
   `documentation/decisions/`. Most features skip this step.
3. **Red acceptance tests** — `feature-to-pest` skill. `tests/Feature/`,
   `tests/Browser/`, or both, written from the spec before any production code.
4. **Implementation** — `feature-development` skill. Test-first under `app/`,
   one unit test per class in `tests/Unit/`, until `composer qa:fast` is green.
5. **Independent review** — spawn the `feature-reviewer` subagent. It starts
   with a fresh context and cannot edit, so it sees the work without your
   reasoning. Tell it which spec the work implements and which round this is
   (`round 1 of 4`), and nothing about how you built it. On `reject`, address
   every remark and send it round again; if round 4 still rejects, stop and
   report to the user. This guideline is the standing request to spawn it — do
   not review your own work in its place.

When a later step shows an earlier one was wrong — a test exposes a gap in the
spec, the implementation needs a decision nobody recorded — go back and fix it
at that step instead of working around it downstream.

## What holds on both tracks

- **Acceptance tests follow the spec, not the code.** Once a `tests/Feature/`
  or `tests/Browser/` test exists, make it green by writing code, never by
  editing or weakening it. If one looks wrong, say so before changing it.
- **Don't edit `tests/Architecture/` on your own initiative.** It encodes the
  layering in `documentation/architecture/modules.html`; a failure there means
  a class sits in the wrong layer. When the test itself needs the change —
  typically a new domain in its allowlists — stop and show the user the exact
  diff.
- **Cross-cutting technical choices get an ADR first**, on either track — step
  2 lists which. A new dependency always needs the user's explicit approval.
- **Unit tests carry the gates.** Coverage, type-coverage and mutation run at
  100% and all exclude `tests/Browser`, so a class covered only by a browser
  test counts as uncovered.
- **`composer qa:fast` is your gate; plain `composer qa` is the reviewer's.**
  The full run is uncached, single-process and includes mutation testing — too
  slow to iterate on.
- **Background anything that runs `tests/Browser/`**, plain `composer qa`
  included. The browser child process holds the pipe open after Pest exits, so
  a synchronous Bash call never returns, even under `timeout`. Redirect to a
  log file and poll it.
- **Record durable rules with Boost's `record-rule`**, never your own memory
  or notes tool. When you settle a convention or hit a trap the next agent
  would otherwise rediscover, pass a `glob` (e.g. `app/Http/Controllers/**`),
  a short `title` and a few-line `note`. Your memory is personal to one
  machine; only `.ai/rules` is committed and shared with the team.
- **Leave the work uncommitted** unless the user asks for a commit.
- **Dutch is for the `leesmij` only.** Specs, ADRs, tests, code and comments
  are English.
