---
paths:
  - 'documentation/**/*.php'
  - 'documentation/**/*.html'
---

# Documentation

## Pint misplaces imports in shebang scripts
In a script that starts with `#!/usr/bin/env php`, Pint's fully_qualified_strict_types fixer inserts `use` statements above `declare(strict_types=1)`, which is a fatal error. After running Pint on such a script, check that declare is still the first statement, and move the import below it. Pint leaves it alone after that.

## documentation/index.html is the hub; every page carries the same top menu
documentation/index.html is the entry point for browsing the docs. Every HTML page under documentation/ starts its <body> with the same <header> menu (cedriekvos/blog documentation → ../index.html, then Architecture · Specifications · Decisions · Lessons), with the page's own section marked aria-current="page". The generated pages (architecture/*.html and specifications/index.html) get it from SECTIONS in documentation/tools/generate.php; ADRs from decisions/template.html; lessons by copying the latest lesson. A new top-level section means adding its link to that menu in all those places plus the hub, and a card on the hub in the same order.

## Specs live in documentation/specifications/, not in their own top-level folders
Gherkin specs are documentation/specifications/features/{domain}/{feature}.feature and their Dutch leesmij documentation/specifications/leesmij/{domain}/{feature}.md. specifications/index.html is the generated feature trace; regenerate it with documentation/tools/generate.php. The scan paths are SPEC_DIR and LEESMIJ_DIR in documentation/tools/trace.php, so a further move changes them there, not in callers.

## Documentation tooling lives in documentation/tools/
Scripts that build or check documentation pages go in documentation/tools/ (generate.php with pages.php, features.php and trace.php; check-lessons.php), never in a section folder: architecture/, specifications/, decisions/ and lessons/ hold only what a reader opens. The tools are outside app/, so the coverage, mutation and PHPStan gates do not apply; composer qa runs them instead.
