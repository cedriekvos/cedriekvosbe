---
paths:
  - 'documentation/features/**'
---

# Features

## Scenario titles are linked to tests by the comment above each test
documentation/architecture/trace.php links each scenario to its tests by parsing the `// {name}.feature — Scenario: {title}` comment directly above each `it()` in tests/Feature and tests/Browser. The title must match exactly; an Outline tested one row per `it()` may add ` (row)`. When you rename or remove a scenario, update those comments in the same change: tests/Architecture/FeatureTraceTest.php fails QA on any break. It runs as its own qa:fast step, outside `--tia`, because TIA ignores comment-only edits to PHP files and does not rerun a test that only reads other files. After changing a spec, leesmij or acceptance test, run `php documentation/architecture/generate.php` and commit the pages it writes: `generate.php --check` fails composer qa and qa:fast while any page is out of date.
