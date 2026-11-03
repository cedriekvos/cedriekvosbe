---
paths:
  - 'documentation/**/*.php'
---

# Documentation

## Pint misplaces imports in shebang scripts
In a script that starts with `#!/usr/bin/env php`, Pint's fully_qualified_strict_types fixer inserts `use` statements above `declare(strict_types=1)`, which is a fatal error. After running Pint on such a script, check that declare is still the first statement, and move the import below it. Pint leaves it alone after that.
