---
name: security-update
description: "Use this skill whenever Composer packages must be updated to clear security advisories. Trigger when the user asks to run composer audit and fix what it reports, or forwards the Composer vulnerability alert email the site sends. Covers: reading composer audit, judging each advisory against this site, the limited composer update, what to do when a patched version conflicts or falls outside a constraint, the composer.lock checks, the full composer qa gate, the commit message, and handing over to the release skill. Does NOT cover npm packages, dependency updates that no advisory asks for, or the release itself (release)."
metadata:
  author: cedriekvos
---

# Security update

You are updating Composer packages until `composer audit` is clean again,
with the smallest change that does it. This is the light track: no spec, no
`leesmij`, no review round. The site mails a Composer vulnerability alert when
an advisory appears, so the user usually starts here from that email.

## 1. Read the audit

Run `composer audit --format=json`. If it lists no advisories, say so and
stop. A listed abandoned package is not an advisory; `composer.json` already
ignores the known ones under `config.policy.abandoned.ignore`.

For each advisory, read its full text. The `link` field names a GitHub
advisory; `gh` is not installed, so fetch it from
`https://api.github.com/advisories/<GHSA-id>`. Note:

- the affected and the first patched versions;
- what an attacker needs, the preconditions;
- whether this site reaches the vulnerable code. Search `app/`, `routes/`,
  `config/` and `resources/` for the API it names, and check whether the
  package is a `require-dev` package that never runs in production.

Write the verdict down in one sentence per advisory, such as "The site never
calls `Concurrency::run()`." It goes into the commit body and the changelog.
Update the package even when the site cannot reach the flaw.

## 2. Update only what the advisories need

```bash
composer update <vendor/package> ... --with-dependencies --dry-run
```

Name only the affected packages. `--with-dependencies` lets their
dependencies move but keeps every other locked package, including other root
requirements, so `composer.json` stays unchanged and the release stays small.
Check the dry run reaches a patched version of each package, then run it
without `--dry-run`, and run `composer audit` again until it reports
"No security vulnerability advisories found."

When the dry run cannot reach a patched version:

- **Another locked package blocks it.** Add that package to the list and say
  why in the commit body. In 1.7.8, Pest 5.3.3 allowed no PHPUnit above
  13.4.3, so the Pest packages had to move with PHPUnit.
- **The patched version is outside the `composer.json` constraint**, usually
  a new major. Stop and ask the user: changing a constraint changes the
  project's dependencies, which needs their approval.

## 3. Check the lock

Read `.ai/rules/composer-lock.md` and follow it; it lists the traps behind
the 1.7.8 and 1.7.9 deploys. In short:

- The deploy installs the latest Composer. Compare `composer --version` with
  `https://getcomposer.org/versions`. If the local one is older, compare
  `Locker::getContentHash()` in `src/Composer/Package/Locker.php` between the
  two tags on `raw.githubusercontent.com/composer/composer/<tag>/`. Identical
  code means the deploy accepts the lock. Otherwise stop: the user rebuilds the
  Sail image on the host with `sail build --no-cache`, and you rerun the update.
- `git diff composer.lock | grep content-hash` prints nothing while
  `composer.json` is unchanged.
- When the update touches `rector/rector`, `phpstan/*`, `larastan` or
  `driftingly/rector-laravel`, the full `composer qa` below runs them uncached.

## 4. Run the full gate

Run plain `composer qa`, not `qa:fast`, in the background with its output in a
log file, and kill `playwright run-server` when it finishes. A dependency
update changes `vendor/`, which `--tia` does not see, and the cached Rector
and PHPStan runs in `qa:fast` let both broken deploys through. If it fails,
find the cause and fix it in its own commit, the way 1.7.10 moved Rector for
the PHPStan that 1.7.8 brought in; code under `app/` follows the
`feature-development` skill. Then run it again.

## 5. Commit the lock on its own

Commit only when the user asked for a commit or a release; otherwise leave the
change uncommitted and say it is ready. Use the `git-commit` skill. Stage
`composer.lock` alone, plus `composer.json` only if the user approved a
constraint change. The message follows the earlier security commits:

```text
build(deps): update laravel/framework to 13.43.0

composer audit reported CVE-2026-128083 (GHSA-63hm-g96r-2m2q, low):
<what the flaw allows>. <the verdict>. composer audit is clean again.

The update was limited to laravel/framework and its dependencies, so
composer.json is unchanged:

- laravel/framework 13.38.0 -> 13.43.0
- <each package that moved, old -> new, grouped by vendor>

Written with Composer 2.11.1. <how the content-hash was checked>. The
full composer qa passes, mutation and browser suites included.
```

With more than two packages, the summary names the group instead, as in
`update symfony, phpunit and pest to clear security advisories`.

## Before you hand over

If the user asked for a release, continue with the `release` skill and give
it the verdicts and the packages that moved, for its `### Security` and
`### Changed` items. Otherwise report what was updated, the verdict per
advisory, and whether `composer qa` passed. When local Composer is older than
the latest release, also tell the user to rebuild the Sail image, even when the
hash still matches.
