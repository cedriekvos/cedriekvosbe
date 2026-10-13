---
name: feature-to-pest
description: "Use this skill whenever a Gherkin .feature spec is turned into Pest acceptance tests — step 3 of this project's full pipeline. Trigger when writing or updating tests under tests/Feature/ or tests/Browser/ from a spec under documentation/features/, or when a spec changed and its acceptance tests must follow. Covers: red-first authoring (write, don't run, never soften), where the test file goes, the Gherkin→Pest mapping (Scenario, Scenario Outline, Background, data tables, Examples), choosing between an HTTP feature test, a Livewire component test and a Pest 4 browser test, reusing and adding tests/Pest.php helpers, traceability comments, and deterministic assertions. Does NOT cover unit tests or production code (feature-development), the spec itself (feature-specifier), or general test-design guidance (testing-best-practices, which this skill builds on)."
metadata:
  author: cedriekvos
---

# Feature to Pest

You are converting one Gherkin `.feature` file into Pest acceptance tests —
**feature** tests, **browser** tests, or both, whichever surface the scenario
actually lives on. **Tests only** at this step — no production code,
migrations, factories, or Livewire components; unit tests come with the
implementation (the **feature-development** skill), and `tests/Architecture/`
is not touched. Don't edit the source `.feature` or its Dutch `leesmij` to
suit the tests; if the spec is wrong or ambiguous, fix the spec first (see
*When the spec isn't enough*).

**Red-first. Do not run anything.** The tests describe behavior that may not
exist yet and are expected to fail. Generate the file(s) and stop — no
`php artisan test`, no `./vendor/bin/pest`, no `--mutate`. Do not soften
assertions to make them pass and do not implement the behavior as part of
this step.

## Where the test goes

Mirror the feature path under the suite you picked (see *Choosing the test
surface*), StudlyCasing the `{feature}` segment and appending `Test`:
`documentation/features/{domain}/{feature}.feature` →
`tests/Feature/{domain}/{StudlyFeature}Test.php`, or
`tests/Browser/{domain}/{StudlyFeature}Test.php` for a browser test. Keep the
`{domain}` path segments exactly as the feature uses them. One test file per
feature file *per suite*: a feature whose scenarios split across both surfaces
gets one file in each, and every scenario lives in exactly one of them.

Invoke the `testing-best-practices` skill before you write anything — it
covers test design: coverage choices, naming, structure, isolation, assertion
quality. For Pest 4 API syntax it does not carry (datasets, `visit()` browser
assertions, Livewire component testing), use `search-docs`. The rules below
are specific to this project's Gherkin→Pest mapping and win where they
differ.

## Mapping Gherkin → Pest

- **Scenario** → one `it('<concise present-tense description>', function () { … })`,
  paraphrased, not the Gherkin title verbatim.
- **Scenario Outline** → a dataset-driven `it()` using `->with([...])` with
  keyed rows; split a row into its own `it()` only when it needs materially
  different setup or assertions.
- **Background** → a `beforeEach()` or a top-of-file helper for state-setting
  steps. Drop purely narrative steps (`Given I am a visitor of the site`).
- **Given** → arrange. **When** → act (the request or component call).
  **Then / And** → assertions.
- A **data table** on a single step → a setup loop/array in the body. An
  **Examples** table → a `->with()` dataset.

## Choosing the test surface

- URL navigation (`I navigate to "/…"`) → HTTP feature test in
  `tests/Feature/`: `$this->get('/…')->assertSuccessful()->assertSee(…)`.
- Livewire component behavior (admin forms/screens) → `tests/Feature/`:
  `Livewire::test(Component::class)->set(…)->call(…)->assert…()`.
- Behavior the HTTP response cannot show, because the browser decides it —
  computed styles, `localStorage`, hover/focus states, a menu that opens
  client-side → Pest 4 browser test in `tests/Browser/`: `visit('/')`, then
  `assertPresent(…)`, `->text(…)`, `->script(…)`. Open such a file with a
  comment saying why the scenarios cannot be asserted over HTTP, the way
  `tests/Browser/navigation/ThemeSwitcherTest.php` does.
- Mirror sibling tests in the same domain for the convention (frontend uses
  `$this->get()`, admin uses `Livewire::test()`, client-side uses `visit()`).

## Setup helpers

- Reuse existing `tests/Pest.php` helpers wherever a step fits
  (`writePostFile()`, `usesFakePostsRepository()`, `makePost()`,
  `actingAs(...)`, `Storage::fake()`, `RefreshDatabase`).
- Add a new helper to `tests/Pest.php` for a recurring step with no existing
  match, mirroring existing style (named, typed parameters, PHPDoc). The
  helper may reference not-yet-existing production code — that's the red.
- A precondition helper that **seeds data binds to how that data is stored**
  (file-backed vs database). That choice belongs to the **feature-architect**
  skill — read the relevant ADR under `documentation/decisions/` first; if
  undecided, **stop and settle it there** rather than guess.
- Use `it()` for every test. **Do not** declare `covers()` — that's the unit
  tests' job.

## Traceability

Immediately above each test, add a one-line comment naming the source feature
file and scenario:

```php
// homepage_about_section.feature — Scenario: The bio is rendered from Markdown
it('renders the bio from Markdown', function () {
```

## Determinism

Keep assertions concrete and deterministic — specific text, status codes,
ordering, redirects, validation errors. Every Gherkin parameter a scenario
asserts must show up as an assertion.

## When the spec isn't enough

- A precondition's storage or format is undecided → settle it with the
  **feature-architect** skill before writing the helper.
- The spec leaves an interface genuinely undefined → go back to the
  **feature-specifier** skill and fix the spec; don't paper over the gap in a
  test.

## Before you move on

Read the `.feature` spec and any ADR that governs it before you start, and
when the tests are written, note what the implementation step needs:

- The production interfaces the tests assume — routes, helper signatures,
  component names, expected markup/selectors. They are the design input for
  **feature-development**.
- Every suite you wrote into, and that the tests are red.
- Whether the feature is browser-only. `composer qa` excludes `tests/Browser`
  from its coverage, type-coverage and mutation gates, so a browser-only
  feature has no unit test to hunt for.
