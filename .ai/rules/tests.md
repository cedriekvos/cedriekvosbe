---
paths:
  - '**/tests/**/Architecture*.php'
---

# Tests

## Pest arch: ignoring() only applies to the last expectation in a chain
`->expect(X)->toBeFinal()->toBeReadonly()->ignoring([...])` applies the ignore list to `toBeReadonly()` only — the earlier expectations still run against the ignored classes and fail.

Give every expectation that needs an ignore list its own `arch(...)` block instead of chaining. Verified on pestphp/pest v5.1.3.
