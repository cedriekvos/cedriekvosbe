---
paths:
  - 'tests/**'
---

# Tests

## Pest arch: ignoring() only applies to the last expectation in a chain
`->expect(X)->toBeFinal()->toBeReadonly()->ignoring([...])` applies the ignore list to `toBeReadonly()` only — the earlier expectations still run against the ignored classes and fail.

Give every expectation that needs an ignore list its own `arch(...)` block instead of chaining. Verified on pestphp/pest v5.1.3.

## Test helpers live in tests/Helpers/, one file per domain
Shared Pest helper functions go in tests/Helpers/{Domain}.php (Blog, Pages, Microblog, AboutMe, Scratchpad), or in Auth.php, Html.php (reading rendered HTML) or Browser.php. Pest 5 loads tests/Helpers/** by itself before Pest.php, so nothing needs to be required. tests/Pest.php holds suite configuration only: TestCase on Unit, TestCase + RefreshDatabase on Feature, BrowserTestCase + RefreshDatabase on Browser. In unit tests, resolve object graphs with app(X::class) rather than hand-wiring them in a helper, so the container stays the only place that wires them.
