---
name: feature-development
description: "Use this skill whenever production code is written or changed under app/ or resources/, or a unit test under tests/Unit/ — step 4 of this project's full pipeline, and the house style for light-track refactors and fixes too. Trigger when implementing a feature against red acceptance tests, adding or reshaping a class, refactoring, fixing a bug, or addressing review remarks. Covers: who owns which test directory, the app/Blog house style (final readonly classes, layering from raw storage to facade, constructor promotion, value objects, types and PHPDoc, constants and guard clauses, functional data handling, naming), the test-first red→green→refactor workflow, the composer qa:fast quality gate and why plain composer qa is off limits, mutation and --tia caveats, running browser tests without hanging, and what to do when the review comes back. Does NOT cover the spec (feature-specifier), acceptance tests (feature-to-pest), cross-cutting technical decisions (feature-architect), or the generic framework conventions in laravel-best-practices, livewire-development, tailwindcss-development and testing-best-practices, which this skill builds on."
metadata:
  author: cedriekvos
---

# Feature Development

You are implementing production code under `app/` **test-first**, so your own
Pest unit tests and the already-written acceptance tests both turn green, in
this project's house style. The canonical reference for that style is the
`app/Blog` namespace — when in doubt, read its sibling files and copy their
shape.

Test ownership splits cleanly:
- **Acceptance tests** — `tests/Feature/` and `tests/Browser/` — were written
  from the spec before the code (the **feature-to-pest** skill). Make them
  green by writing code, never by editing or weakening them.
- **Architecture tests** (`tests/Architecture/`) encode the layering rules in
  `documentation/architecture/modules.html` and are not yours to edit here. A
  failure there means your class graph is in the wrong layer — redesign it, or
  settle it with the **feature-architect** skill if the layering itself is the
  question. When the layering is settled and the test just needs the change —
  typically a new domain added to its allowlists — only the user can make that
  call: stop and ask (see *Boundaries*).
- **Unit tests** (`tests/Unit/`) are yours, and carry **all** coverage,
  type-coverage, and mutation responsibility. One unit test per production
  class, mirroring its `app/` path, with `covers(TheClass::class)` at the top.

## Use the project's skills

This project mandates domain skills — invoke them before writing the kind of
code they cover, not after you get stuck: `laravel-best-practices` for any
Eloquent/controller/job/queue code, `testing-best-practices` for every unit
test you write, `livewire-development` for any Livewire component, and
`tailwindcss-development` for Tailwind markup. They carry the current
framework APIs and conventions; the house-style rules below are this
codebase's specific spin on top of them, not a replacement for them. They are
generated into `.claude/skills/` by Boost and gitignored — if a `Skill` call
finds nothing, tell the user rather than guessing at the conventions; they
restore them with `php artisan boost:install --skills`.

## House style (modelled on `app/Blog`)

The layering itself is documented in `documentation/architecture/modules.html`
(with `anatomy.html` and `dependencies.html` beside it) and enforced by
`tests/Architecture/`. Read it when a new class's layer is not obvious; what
follows is the short version plus the conventions those tests cannot check.

**File & class skeleton** — `<?php`, blank line, `declare(strict_types=1);`,
blank line, `namespace`, always. Default every class to `final readonly`. One
class per file, named for its single responsibility.

**Single responsibility & composition** — many tiny classes wired together,
not a few big ones. Mirror the `app/Blog` layering when it fits: raw storage →
format (parse/serialize/convert) → source + factory (build domain objects) →
read/write repositories → a thin **facade** that only delegates. Split reads
from writes (`…GetRepository` vs `…WriteRepository`), re-unify behind a
logic-free facade (`…Repository`). Prefer a new small class over growing an
existing one.

**Constructors & DI** — constructor property promotion always: dependencies
`private`, value-object data `public`. One param per line, trailing comma,
empty `{}` body on the same line. Property name mirrors the type in camelCase.
Rely on container autowiring.

**Value objects** — like `Post`: `final readonly`, all data as public promoted
properties, no behavior. Boolean flags mirroring data keys are `is_*`
snake_case; method parameters for the same concept are camelCase.

**Types & PHPDoc** — explicit return type on every method, type hint on every
parameter. PHPDoc only for what the signature can't express — array shapes:
`@param array<int, Post>`, `@param array{title: string, date: string} $attrs`.
`mixed` only for genuinely untyped data, then narrow it.

**Constants, control flow, errors** — magic values as typed `private const`
SCREAMING_SNAKE_CASE. Guard clauses over nested `if`/`else`; curly braces
always. Throw `RuntimeException` for invariant violations with the value in
`[brackets]`. Degrade gracefully where a failure shouldn't crash the page
(`report($e);` then a safe fallback).

**Functional, immutable data handling** — `array_map`/`array_filter`/`usort`
with first-class callable syntax, `array_values(...)` after filtering, typed
arrow functions for short predicates.

**Naming, comments, idioms** — classes are noun + role, methods are
descriptive verbs, no abbreviations. PHPDoc over inline comments. Laravel
idioms: `Storage::disk()`, `config()`, `Str::of()->…->value()`.

**Honor ADRs.** Cross-cutting technical choices are recorded under
`documentation/decisions/`. Read the relevant ADRs before designing and follow
them. If the work needs such a decision and no ADR covers it, settle it with
the **feature-architect** skill rather than deciding alone — this is also why
you don't add dependencies without approval. You still own the per-feature
class graph within the strategy an ADR sets.

## Workflow — test-first (red → green → refactor)

1. **Read the spec and the red acceptance tests first** — they define the
   exact public surface (facade class name, method signatures, return shapes).
   Let them drive the design.
2. **Sketch the class graph** following the `app/Blog` layering; identify the
   thin facade the acceptance tests talk to.
3. **Build each collaborator test-first**, one red-green-refactor cycle at a
   time: red unit test in `tests/Unit/` with `covers()` (scaffold with
   `php artisan make:test --pest --unit {Name} --no-interaction`, confirm it
   fails for the right reason) → minimum production code to pass it (scaffold
   with `php artisan make:class … --no-interaction`) → refactor while green.
   Between cycles, run `composer qa:fast` for quick feedback — it's parallel
   and TIA-accelerated (`--tia`), so it only re-executes what your latest edit
   actually touched.
4. **Wire up the facade**, confirm the acceptance tests pass:
   `php artisan test --compact --filter=<name>`. If they include
   `tests/Browser/` tests, run those the way *Running browser tests* below
   describes — never on the Bash tool's synchronous path.
5. **Format and auto-fix**: `composer fix-qa`, or at minimum
   `vendor/bin/pint --dirty --format agent`.
6. **Run the gate:** `composer qa:fast` — must pass end to end.

## Quality gate — `composer qa:fast` must be green

`composer qa:fast` is your gate: `composer validate`, `npm run build`, `rector
--dry-run` + `pint --test`, `phpstan analyse`, `pest --parallel --tia`, then
coverage and type-coverage at `--min=100`, both `--exclude-testsuite=Browser`.
Write unit tests as the thing that carries coverage: browser tests are
excluded from the coverage, type-coverage and mutation gates entirely, so a
class covered only by `tests/Browser/` counts as uncovered. Every line and
branch must exist to satisfy a unit test you wrote first. A feature with no
PHP of its own — CSS, Blade markup, an inline script — has no class to unit
test; say that plainly instead of inventing a test.
`composer qa:fast` green is your per-round gate, not a declaration of done —
on the full pipeline, acceptance is the **feature-reviewer** subagent's
verdict.

**Never run the plain `composer qa`** — the uncached, non-parallel run is far
too slow for your loop. The reviewer runs it for real; your job is to hand
over work that will survive it. Two consequences to work with, not around:

- **Mutation testing isn't in `qa:fast`.** Write unit tests as if
  `pest --mutate --min=100` were watching, because at review time it is: kill
  every mutant with a unit test on the class that owns it, never suppress one
  and never lean on a feature test to close the gap. To check one class you're
  unsure about, run it narrowly yourself —
  `vendor/bin/pest --mutate --covered-only --filter=<TestName>` — rather than
  reaching for the full gate.
- **`--tia` can replay a cached result** instead of truly re-executing a test.
  If a pass looks suspicious, re-run that suite directly
  (`vendor/bin/pest --testsuite=Unit`, or `--filter=<name>`) — still never the
  full `composer qa`.

## Running browser tests

`tests/Browser/` spawns a real browser, and the child process keeps the pipe
open after Pest exits: run one on the Bash tool's normal synchronous path and
the call never returns, even under `timeout`. Always background it and read
the file instead:

```sh
./vendor/bin/pest tests/Browser/<path>Test.php --compact > /tmp/browser.log 2>&1 &
```

then poll `/tmp/browser.log`. `composer qa:fast` does not run the browser
suite, so this only comes up when you are verifying acceptance tests that
live in `tests/Browser/`.

## Boundaries — escalate instead of guessing

- Don't write or edit specs as part of implementing; if the requirement is
  genuinely under-specified, go back to the **feature-specifier** skill and
  fix the spec first.
- Own unit tests, not acceptance tests — never edit or weaken a
  `tests/Feature/` or `tests/Browser/` test to make it pass; if one looks
  wrong, say so and let the user or the reviewer settle it.
- Stay under `app/`, `resources/`, and `tests/Unit/`. Don't touch
  `tests/Feature/`, `tests/Browser/`, `tests/Architecture/` or
  `documentation/` while implementing, and don't add dependencies without an
  ADR approving them.
- When what stands between you and green is a change you may not make
  yourself, such as an edit to `tests/Architecture/`, stop and ask the user:
  give the exact change as a ready-to-apply diff, and say what is already
  green. Don't route around it through an ADR or a spec change — neither
  makes that edit legitimate.
- **Don't commit** unless the user asks. The work stays in the working tree:
  the reviewer reads it uncommitted, and the user commits after the review.

## When the review comes back

On the full pipeline the **feature-reviewer** subagent returns `approve` or
`reject` with a remarks checklist. On `reject`, resolve every remark without
regressing what was green; if you believe a remark is wrong, say so explicitly
to the user — never silently skip it. Then get `composer qa:fast` green again
before the next round.
