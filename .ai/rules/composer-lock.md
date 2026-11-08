---
paths:
  - composer.lock
---

# Composer Lock

## Refresh composer.lock when a Composer release changes its hash
Composer is not pinned: the Buddy deploy installs the latest release on every run, and the Sail image keeps the version it was built with. A Composer minor can change how the lock's content-hash is computed (2.11 started hashing config.policy), and then `composer validate` in `composer qa` fails in CI on a lock nobody touched, while it still passes locally. To fix it, rebuild the Sail image (`sail build --no-cache`) so local Composer matches the version the Buddy log shows, run `composer update --lock`, and commit the lock on its own; the locked packages and references must not change. For the same reason, compare `composer --version` with https://getcomposer.org/versions before committing any lock change.

## Run Rector and PHPStan uncached after a tooling update
`qa:fast` runs Rector and PHPStan with their caches, so unchanged files are not re-analysed and a broken tool upgrade still passes locally. `composer qa` in CI runs them uncached. When a lock change touches rector/rector, phpstan/*, larastan or driftingly/rector-laravel, also run `./vendor/bin/rector process --dry-run --clear-cache` and `./vendor/bin/phpstan clear-result-cache && ./vendor/bin/phpstan analyse`. Rector ships its own copy of phpstan/phpdoc-parser and loads it before vendor's, so a newer PHPStan can require a newer Rector (2.8.4 for PHPStan 2.3.5).
