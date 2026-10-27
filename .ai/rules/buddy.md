---
paths:
  - '.buddy/**'
---

# Buddy

## Buddy actions share the filesystem that rsync deploys
Every action in a Buddy pipeline works in the same pipeline filesystem, and the RSYNC action uploads all of it with --delete. Anything a build or QA action creates (vendor/ with dev packages, bootstrap/cache manifests, .env) reaches production unless it is listed in deployment_excludes, so add the path there when an action starts producing it. The browser suite leaves `playwright run-server` running after Pest exits; kill it at the end of the QA action so the action can finish.
