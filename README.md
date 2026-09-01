# cedriekvos.be

Personal site of Cedriek Vos — a blog, a microblog, an about page and a scratchpad.

All content is Markdown on disk. There is no CMS and no content database: every post,
message and page is a file with YAML front matter, edited through a small Livewire admin
and rendered on request.

Built on PHP 8.4, Laravel 13, Livewire 4 and Tailwind 4.

## How it works

### Content lives in files

Each domain reads and writes through its own filesystem disk. SQLite backs only the
editor account, sessions, cache and queue — never the content.

| Domain             | Disk        | Path                                     |
| ------------------ | ----------- | ---------------------------------------- |
| Blog               | `posts`     | `storage/app/private/content/posts`      |
| Microblog          | `microblog` | `storage/app/private/content/microblog`  |
| About & Scratchpad | `meta`      | `storage/app/private/content/meta`       |
| Vulnerability mutes| `security`  | `storage/app/private/security`           |

Slugs and ids address files on disk, so the routes constrain them to the shapes the admin
forms can produce — a path separator or a dot never reaches storage.

### Three layers

The `App\` namespace is arranged in three bands, and `tests/Architecture` fails the build
when the boundaries are crossed:

- **Delivery** — `Console`, `Http`, `Livewire`, `Mail`, `View`. The only layer that knows
  about requests. Entry points stay thin: resolve a domain collaborator, hand back a view,
  a redirect or an exit code.
- **Bounded domains** — `About`, `Blog`, `Microblog`, `Scratchpad`, `Security`.
  Self-contained, file-backed, and unaware of HTTP. Each is built from the same roles:
  a model, a Markdown codec, `Repositories/`, and `Storage/`.
- **Shared kernel** — `Markdown`. The front matter codec and Markdown-to-HTML conversion
  every content domain builds on.

The generated module map, dependency graph and flow traces live in
[`documentation/architecture/`](documentation/architecture/index.html).

## Requirements

- PHP 8.4 with Composer
- Node.js with npm
- SQLite

Or run everything in Docker with [Laravel Sail](https://laravel.com/docs/sail) — the image
is defined in `docker/8.4/` and the services in `compose.yaml`.

## Getting started

```bash
composer setup     # install, .env, key, migrate, npm install, npm run build
php artisan db:seed   # local only: creates the editor account
composer dev       # serve + queue worker + logs + vite, all at once
```

The seeder creates `test@example.com` with the factory's shared development password. It
refuses to run outside `local` and `testing`, because login is the site's only
authentication surface and seeding elsewhere would plant a known-credential editor account.

With Sail:

```bash
./vendor/bin/sail up -d
./vendor/bin/sail composer setup
```

The container is itself an `artisan serve` on port 80, so `composer dev` would
otherwise start a second, unreachable server. `AppServiceProvider` drops that one
process in the `local` environment; the queue listener, log tail and Vite still run.

The public site is at `/`, the admin at `/admin` behind login.

## Everyday commands

| Command                | What it does                                              |
| ---------------------- | --------------------------------------------------------- |
| `composer dev`         | Queue listener, log tail and Vite; a server outside `local`  |
| `php artisan dev:list` | Show which dev processes `composer dev` will start          |
| `composer test`        | Clear config, then run the suite                            |
| `composer qa`          | The full gate — see below                                   |
| `composer qa:fast`     | Parallel subset for the inner loop, no mutation run         |
| `composer fix-qa`      | Apply Rector and Pint fixes                                 |
| `composer phpmd`       | Clean-code ruleset (run `phpmd:install` once first)         |

## Testing

Four Pest suites, split so each can run alone:

- `Unit` — domain classes in isolation
- `Feature` — HTTP and Livewire behaviour
- `Browser` — real-browser journeys via Playwright (`npx playwright install` once)
- `Architecture` — layer boundaries, naming, and the PHP/security/Laravel presets

```bash
php artisan test --compact                       # everything
php artisan test --compact --filter=PostSorter   # one thing
vendor/bin/pest --testsuite=Unit                 # one suite
```

`composer qa` is the merge gate. Beyond Rector, Pint and PHPStan it holds three hard
thresholds — **100% code coverage, 100% type coverage and a 100% mutation score** — then
builds the frontend and runs the browser suite last.

## Security alerts

`CheckComposerVulnerabilitiesCommand` runs hourly (`routes/console.php`), audits the
installed Composer packages and emails new advisories. Set the recipient with:

```dotenv
SECURITY_ALERT_RECIPIENT=you@example.com
```

Leave it empty and the check still runs, but sends nothing. Advisories can be muted for a
window; that state is JSON on the `security` disk.

## Repository layout

```
app/                  delivery layer, bounded domains, shared kernel
documentation/
  features/           Gherkin specs, one per feature
  leesmij/            Dutch plain-language companion to each spec
  decisions/          ADRs
  architecture/       generated module map, dependencies and flow traces
pack/                 autonomous multi-agent pipeline (see pack/README.md)
tests/                Unit, Feature, Browser, Architecture
```

## Documentation

Every feature carries a Gherkin spec in `documentation/features/` and a Dutch `leesmij`
that explains it in plain language. Cross-cutting technical decisions are recorded as ADRs
in `documentation/decisions/` — start at the [index](documentation/decisions/index.html).

## Agent pack

`pack/` is a self-contained multi-agent pipeline that can drive a feature from spec to
reviewed implementation: specifier → architect → tests → development → review, handing off
through a queue and archiving a full audit trail per run. It is separate from the
application. See [`pack/README.md`](pack/README.md).
