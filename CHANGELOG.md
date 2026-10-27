# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.7.6] - 2026-10-27

Documentation and QA only: the architecture site traces every feature from its
`leesmij` to its tests, and QA fails when that chain breaks or a generated page
goes stale. The site itself is unchanged.

### Added

- **Documentation** — a Feature trace tab on the architecture site follows each
  feature from its Dutch `leesmij`, through its Gherkin scenarios, to the
  acceptance tests that check them, down to each test's code. It lists every
  place the chain breaks, and the acceptance tests no scenario describes. The
  link from scenario to test is the existing `// x.feature — Scenario: …`
  comment above each test.
- **Tests** — `tests/Architecture/FeatureTraceTest.php` fails when a scenario
  has no test, a test comment names no scenario, a test file matches no spec,
  a spec and its `leesmij` lack each other, or a `leesmij` links to no
  feature. `qa:fast` runs it as its own step, outside `--tia`: TIA ignores
  comment-only edits and would replay its last result after a test is deleted.
- **QA** — `generate.php --check` fails `qa` and `qa:fast`, and with them the
  Buddy deploy, when a committed page under `documentation/architecture/`
  differs from what the sources generate now. A change to `app/`, `routes/`,
  a spec, a `leesmij` or an acceptance test now regenerates the pages in the
  same commit. The commit stamp is left out of the comparison.
- `.ai/rules/features.md` records that a test comment repeats its scenario's
  title exactly, and that renaming a scenario updates those comments.

### Changed

- **Documentation** — each architecture page is stamped and footed with its own
  sources, so the feature trace names the specs and tests it was read from
  instead of `app/`.

## [1.7.5] - 2026-10-27

Deployment only: the production pipeline runs the full QA before it deploys.
The site itself is unchanged.

### Added

- **Deployment** — a `composer qa` action between the asset build and rsync,
  so a failing check stops the pipeline before anything reaches the server.
  It runs on Ubuntu 24.04 with PHP 8.4, PCOV and Node 24, like the Sail
  container, and stops the `playwright run-server` process the browser suite
  leaves behind, which would otherwise keep the action from finishing.
- `.ai/rules/buddy.md` records that every Buddy action shares the filesystem
  rsync uploads, so anything an action creates needs an exclude.

### Changed

- **Deployment** — rsync excludes `/vendor/` and `/bootstrap/cache/*`. The QA
  run's dev packages and package manifests stay out of production, and rsync
  no longer deletes the server's `vendor/` before `composer install` puts it
  back, which left the site failing in between.

## [1.7.4] - 2026-10-27

Deployment and setup only: the production pipeline moves from Buddy's web
interface into the repository, and new installs default to file sessions. The
site itself is unchanged.

### Added

- **Deployment** — `.buddy/deploy-production.yml` defines the production
  pipeline: build the assets, rsync them to the server, then run
  `composer install`, the migrations and the Laravel caches over SSH. Buddy
  autodiscovers it, so the repository replaces the pipeline configured in the
  web interface as the source of truth. The `cedriekvosbe-production` target
  stays in Buddy with its host, path and SSH key.

### Changed

- **Setup** — `.env.example` sets `SESSION_DRIVER=file` instead of
  `database`. Public pages never use the session, yet the database driver gave
  every visitor and crawler a row in the SQLite sessions table; measured
  locally, file sessions cut `/login` from 77 to 24 ms and the homepage from
  137 to 79 ms. Existing `.env` files keep their driver; only a new install
  through `composer setup` picks this up.

## [1.7.3] - 2026-10-24

Internal work only: the admin components get unit tests, npm insists on
Node 24, and the Dutch `leesmij` notes are cut back to their essence. The site
itself is unchanged.

### Added

- **Tests** — a unit test for each of the eight Livewire components in
  `app/Livewire/Admin`, so mutation testing now reaches them. Every component
  scores 100%.

### Changed

- **Build** — `package.json` requires Node 24 through `devEngines.runtime`, so
  npm stops with `EBADDEVENGINES` before `install`, `ci` and `run` on any other
  major. Node 24 was only implied by the Sail Dockerfile and the Buddy image;
  a new Node major now means updating all three together.
- **Admin** — the post, page and message forms drop the validation rules their
  typed properties already enforce (`string`, `boolean`, `nullable`, and the
  `isDraft` and `isFeatured` entries). Saving a post reads `isFeatured` from
  the property, like `isDraft`. What the forms accept and reject is unchanged.
- **Documentation** — the module map draws Pages as a bounded domain next to
  the other four instead of filing `App\Pages` under Platform, so every
  dependency arrow points downward.
- **Documentation** — every Dutch `leesmij` is cut back to a short intro and
  one bullet per decision, from about 9,800 words to 2,900. The
  `feature-specifier` skill and a new rule in `.ai/rules/leesmij.md` keep new
  ones that short.
- `documentation/refactor.md` lists the module map and the admin unit tests
  under done work.

## [1.7.2] - 2026-10-24

Maintenance: a Livewire security advisory is cleared, every Composer and npm
dependency is on its latest minor or patch release, and `composer audit`,
`composer outdated` and the browser tests run cleanly again. The site itself
is unchanged.

### Added

- **Composer** — `composer audit:stale-ignores` fails as soon as a package on
  the abandoned-package ignore list is no longer installed or no longer
  abandoned, so the ignore is removed once it is no longer needed. It runs
  after every `composer update`, `require` and `remove`, and in `qa` and
  `qa:fast`.
- **Tests** — `composer playwright:install` installs the Chromium build the
  installed Playwright needs and takes about a second when it is already
  there. `setup`, `test`, `qa` and `qa:fast` run it first, so the browser
  tests no longer fail after a Playwright update or an `npm ci`, which remove
  the browsers from `node_modules`.

### Changed

- **Dependencies** — every Composer and npm package moves to its latest
  release within the existing constraints; `composer.json` and `package.json`
  keep their ranges. Among others: Laravel 13.34.0 to 13.38.0, Pest 5.1.3 to
  5.3.3, Vite 8.2.2 to 8.3.5, Tailwind CSS 4.2.2 to 4.3.3 and Playwright
  1.61.1 to 1.64.1.
- **Composer** — `composer audit` ignores the abandoned
  `symplify/rule-doc-generator-contracts`, a dev-only package of interfaces
  that `driftingly/rector-laravel` still requires, so the audit passes again.
- **Composer** — the versions of `cedriekvos/laravel-composer-audit`, which is
  not on Packagist, are read from a git clone over HTTPS (`no-api`) instead of
  the GitHub API, whose anonymous limit of 60 requests an hour ran out after a
  few `composer update` and `composer outdated` runs. `composer update` now
  needs git; `composer install` and the deploy still download the zip from
  `composer.lock`.
- **Agent workflow** — the instruction to record durable rules with Boost's
  `record-rule` moved into `.ai/guidelines/workflow.md`, because Boost 2.10
  dropped it from the guidelines it generates.

### Security

- **Livewire** updated from 4.4.4 to 4.4.8, clearing PKSA-xhv3-nr94-h8db
  (GHSA-mhf7-xp6r-6qrc, medium, affecting 4.3.0 up to 4.4.7): an
  authorization bypass in the `#[Authorize]` attribute through reordered
  parameter keys and the execution order of `#[Json]`. The site uses neither
  attribute, and its admin components sit behind the `auth` middleware on
  their routes, so it was not exposed.
- **Frontend build tooling** — the npm update clears the advisories in
  `shell-quote` through `concurrently` (critical), and in Vite and
  `source-map-js` (high). They affect the development server and the build,
  not the deployed site. One moderate advisory remains open:
  `postcss-selector-parser` (GHSA-rj75-hqrm-r3gf), which
  `@tailwindcss/typography` pins to 6.0.10 with no fixed release yet. It only
  parses the project's own CSS during the build.

## [1.7.1] - 2026-10-20

Internal clean-up from an architecture review: the test helpers, the Markdown
converters and the admin forms are reorganised. The site itself is unchanged.

### Added

- `documentation/refactor.md`, a backlog of the open points from the review,
  each with its files, the problem, a proposal and what has to be decided
  first.
- **ADR 0009** — the converter without code highlighting lives in the shared
  `App\Markdown` kernel. It corrects one rule of ADR 0003; the About bio still
  never gets highlighting.
- **Agent skills** — `codebase-design`, `grilling` and `domain-modeling` from
  `mattpocock/skills`, which `improve-codebase-architecture` calls. Two new
  rules in `.ai/rules/` keep ADRs as HTML pages in `documentation/decisions/`
  and require approval before tests are deleted, where those skills say
  otherwise.

### Changed

- **Tests** — the shared Pest helpers moved out of `tests/Pest.php` into one
  file per domain under `tests/Helpers/`, which Pest loads by itself.
  `Pest.php` now only configures the suites. Browser tests run on their own
  `BrowserTestCase`, and unit tests no longer migrate a database they never
  use.
- **Tests** — acceptance tests read rendered HTML with PHP's HTML5 parser and
  CSS selectors, through `htmlElement()` and `htmlElements()`, instead of
  regex helpers. The header-link tests now look only inside `<header>`.
- **Markdown** — the About bio and Pages render through one converter,
  `App\Markdown\ProseMarkdownToHtmlConverter`. It replaces a Blog class that
  only About used and its copy in Pages, so About no longer depends on Blog,
  and the architecture tests now forbid it.
- **Admin** — the post and page forms ask the domain whether a slug is taken,
  instead of applying the `draft-` prefix and checking for a collision
  themselves.
- **Agent skills** — `improve-codebase-architecture` updated to the current
  upstream version.
- The module map under `documentation/architecture/` is regenerated.

## [1.7.0] - 2026-10-15

The header's GitHub logo now matches the LinkedIn logo in pure black or white,
and a dependency with an open security advisory is updated to a patched
version.

### Changed

- **Navigation** — the GitHub logo in the header is pure black in light mode
  and pure white in dark mode, the same as the LinkedIn logo. It used to take
  the theme's text colour, near-black `#1f2328` and light grey `#c9d1d9`, which
  looked washed out next to it. Site text and the theme toggle keep their
  colours.

### Security

- **symfony/http-foundation** updated from 8.1.6 to 8.1.9, clearing
  CVE-2026-108082 (PKSA-xmwy-rmx5-m7h4, fixed in 8.1.7): a regular-expression
  denial of service in `HeaderUtils::split()`. Every incoming request with a
  `Cache-Control` header is parsed through that method, so any visitor could
  reach it without logging in. The advisory gives no severity.

## [1.6.0] - 2026-10-13

The header now links to the LinkedIn profile next to the GitHub one, and a
divider sets both apart from the theme switcher.

### Added

- **Navigation** — a LinkedIn profile link in the header, directly after the
  GitHub link and opening in a new tab. It shows the official logo, the black
  variant in light mode and the white one in dark mode.
- **Navigation** — a thin vertical divider between the profile links and the
  theme switcher, in the header's border colour so it follows the theme.

### Changed

- **Agent workflow** — the pipeline that takes a feature from spec to reviewed
  implementation now runs in a single agent session instead of the autonomous
  tmux pack. A workflow guideline picks between the full pipeline and a light
  track for changes that add no visible behaviour; the `feature-specifier`,
  `feature-architect`, `feature-to-pest` and `feature-development` skills carry
  each step's conventions, and a read-only `feature-reviewer` subagent does the
  independent review. The pack in `pack/` is untouched.

## [1.5.0] - 2026-10-08

The Composer vulnerability alert email now says which application it is about.
The site itself is unchanged.

### Changed

- **cedriekvos/laravel-composer-audit** updated from 1.0.0 to 1.1.0. The
  alert's subject is now `[<APP_NAME>] Composer vulnerability alert` instead of
  `Composer vulnerability alert`, and its body names the `APP_URL` the audit
  ran for, so alerts from several projects can be told apart. Update any mail
  filter that matches the old subject exactly, and check that `APP_NAME` and
  `APP_URL` are set where the site is deployed: with the defaults from
  `.env.example` the alert reads `[Laravel]` and `http://localhost`. This
  project does not publish the package's email template, so the app needs no
  change.

## [1.4.1] - 2026-10-04

Two dependencies with open security advisories are updated to patched
versions. The site itself is unchanged.

### Security

- **league/commonmark** updated from 2.10.0 to 2.10.3, clearing two advisories
  fixed in 2.10.2: a quadratic-time denial of service in the GFM Table
  extension (GHSA-3q6v-r5mr-hxv8, high) and a `DisallowedRawHtml` bypass that
  allowed stored XSS (GHSA-97jj-33gv-5xf9, medium). The site's own Markdown
  converters load neither extension and escape raw HTML, so posts, pages and
  messages were not rendered through the affected code.
- **Laravel** updated from 13.29.0 to 13.34.0, clearing CVE-2026-102279
  (GHSA-jh5r-qr3c-85q8, low, fixed in 13.30.0): a DOM-based XSS on the debug
  page, reachable only with `APP_DEBUG=true`.

## [1.4.0] - 2026-09-30

Preview: see a draft post or page as visitors will, before publishing it.

### Added

- **Admin** — a `[preview]` button in the editor of a saved post or page,
  opening `/admin/posts/{slug}/preview` or `/admin/pages/{slug}/preview` in a
  new tab. The preview renders the post or page with its public template,
  marked with a notice that it is not yet visible to visitors, and works for
  drafts and published content alike. It sits behind the admin login; drafts
  stay 404 on their public URL, and an unknown or malformed slug is a 404.

## [1.3.0] - 2026-09-21

Pages: standalone Markdown content, such as an about page or a colophon, served
at the root of the site instead of under `/blog/`.

### Added

- **Pages** — Markdown files with YAML front matter on a new `pages` disk
  (`storage/app/private/content/pages`), stored like posts, drafts under a
  `draft-` slug prefix. A published page is served at `/{slug}` with its body
  rendered as Markdown; drafts and unknown slugs return 404. An existing route
  always wins over a page with the same slug, so a page slugged `login` is
  never reachable.
- **Admin** — a `[pages]` screen listing every page, drafts labelled, with edit
  and delete, and one form for creating and editing a page. The form has only a
  title, slug and body, since a page has no date, excerpt or featured flag. New
  pages start as drafts.
- **Agent pack** — an agent can stop the pack and ask for the human. A verdict
  routed to the new `HALT` edge target halts the pack and names the handoff to
  read; clearing it with `[x]` hands the task back to the agent that asked.
  `feature-development` uses it, as `needs_human`, for a change no agent may
  make, such as one to `tests/Architecture/`, instead of passing the job round
  the loop. Pack version 6.

## [1.2.0] - 2026-09-10

The hourly Composer vulnerability alert now ships as its own package. What it
does is unchanged; where it lives, and the name of one setting, are not.

### Added

- `.ai/rules/`, shared rules for agents and contributors, starting with one
  Pest trap: in an arch chain, `ignoring()` only applies to the last
  expectation.

### Changed

- The alert moved out of the app into `cedriekvos/laravel-composer-audit`,
  installed from its GitHub repository at `^1.0`. The package registers the
  `security:check-vulnerabilities` command, its hourly schedule and its own
  storage disk itself, and still mutes each advisory for 48 hours after it is
  reported. ADR 0008 records the move and supersedes ADR 0001.
- **The recipient setting is renamed.** Set
  `LARAVEL_COMPOSER_AUDIT_ALERT_RECIPIENT` instead of `SECURITY_ALERT_RECIPIENT`
  when deploying: under the old name the check still runs but sends nothing.
  The mute state moves from `storage/app/private/security` to
  `storage/app/private/laravel-composer-audit`, so the first run after deploying
  reports every open advisory once more.

### Removed

- `App\Security`, its console command, mailable, mail view, config file,
  `security` disk, tests, Gherkin spec and Dutch `leesmij`. They live in the
  package now, which carries its own tests and quality gate.

## [1.1.1] - 2026-09-09

Both changes are to the agent pack in `pack/` — the multi-agent pipeline that
drives a feature from spec to reviewed implementation. The site itself is
unchanged.

### Fixed

- The pack handed the same work to two agents. Its Stop hook looked for an
  agent's handoff only in `queue/pending/`, but the dispatcher moves it to
  `runs/<task>/` the moment it routes it, so an agent that stopped inside that
  window was told its handoff was missing and wrote a second one. A handoff now
  counts as filed whether it is still queued or was just archived for the
  round. Pack version 5.

### Changed

- Every delivery message carries `loop <n>/<max>`, not only rejections, so an
  agent can tell when a `reject` would halt the pack rather than come back
  round to it. `HANDOFF.md` drops the "overwrite your own pending file" escape
  hatch that a second delivery hid behind.
- The agent prompts now match what the agents are actually allowed to do.
  `tests/Browser/` counts as an acceptance suite alongside `tests/Feature/`,
  both of them and `tests/Architecture/` are off-limits to
  `feature-development`, and each prompt's tool list spells commands both as
  `vendor/bin/...` and `./vendor/bin/...` so an unattended agent never stalls
  on a permission prompt.

## [1.1.0] - 2026-09-09

### Changed

- The theme switcher button no longer mirrors the active mode. It always shows
  the auto icon, so it reads as "open the theme menu" instead of doubling as a
  mode indicator; the checkmark in the open menu is now the only place the
  active mode is shown. The button also drops its border and its colour-change
  hover in favour of the scale-up hover the GitHub link already used, and the
  per-mode icons are gone from the menu options, leaving plain labels.
- Livewire updated to 4.4.4, a routine patch with no security impact. This
  project was never affected by CVE-2026-81887 (DOM-based XSS, fixed in 4.3.4),
  having been on 4.4.3 already.

## [1.0.1] - 2026-09-04

### Fixed

- Two stale notes in the Pest test helpers claimed the scratchpad domain did not
  exist and the about-me storage interface was unsettled. Both had shipped in
  1.0.0; the helpers now describe the repositories they actually seed through.

## [1.0.0] - 2026-09-01

First tagged release. Content lives as Markdown files on disk; SQLite backs only
the editor account, sessions, cache and queue.

### Added

- **Blog** — Markdown posts with YAML front matter on the `posts` disk: homepage
  list, featured post, post detail, reading time, drafts, and a `/blog` redirect.
  Fenced code blocks are syntax highlighted.
- **Microblog** — short messages keyed by ULID on the `microblog` disk, rendered
  as plain text with bare web URLs auto-linked.
- **About me** — an editable bio section on the homepage, stored on the `meta` disk.
- **Scratchpad** — a private admin note kept alongside the about-me content.
- **Admin** — Livewire forms and listings for posts, messages, the bio and the
  scratchpad, behind a single-user Breeze login.
- **Security** — an hourly `security:check-vulnerabilities` command that audits
  installed Composer packages and emails new advisories, muting each one for 48
  hours after it is reported.
- **Navigation** — a light/dark/auto theme switcher and a GitHub profile link in
  the header.
- **Quality** — a `composer qa` gate running Rector, Pint, PHPStan and four Pest
  suites, holding 100% code coverage, type coverage and mutation score.
- **Documentation** — a Gherkin spec and Dutch `leesmij` per feature, seven ADRs,
  and a generated architecture site.

[Unreleased]: https://github.com/cedriekvos/cedriekvosbe/compare/1.7.6...HEAD
[1.7.6]: https://github.com/cedriekvos/cedriekvosbe/compare/1.7.5...1.7.6
[1.7.5]: https://github.com/cedriekvos/cedriekvosbe/compare/1.7.4...1.7.5
[1.7.4]: https://github.com/cedriekvos/cedriekvosbe/compare/1.7.3...1.7.4
[1.7.3]: https://github.com/cedriekvos/cedriekvosbe/compare/1.7.2...1.7.3
[1.7.2]: https://github.com/cedriekvos/cedriekvosbe/compare/1.7.1...1.7.2
[1.7.1]: https://github.com/cedriekvos/cedriekvosbe/compare/1.7.0...1.7.1
[1.7.0]: https://github.com/cedriekvos/cedriekvosbe/compare/1.6.0...1.7.0
[1.6.0]: https://github.com/cedriekvos/cedriekvosbe/compare/1.5.0...1.6.0
[1.5.0]: https://github.com/cedriekvos/cedriekvosbe/compare/1.4.1...1.5.0
[1.4.1]: https://github.com/cedriekvos/cedriekvosbe/compare/1.4.0...1.4.1
[1.4.0]: https://github.com/cedriekvos/cedriekvosbe/compare/1.3.0...1.4.0
[1.3.0]: https://github.com/cedriekvos/cedriekvosbe/compare/1.2.0...1.3.0
[1.2.0]: https://github.com/cedriekvos/cedriekvosbe/compare/1.1.1...1.2.0
[1.1.1]: https://github.com/cedriekvos/cedriekvosbe/compare/1.1.0...1.1.1
[1.1.0]: https://github.com/cedriekvos/cedriekvosbe/compare/1.0.1...1.1.0
[1.0.1]: https://github.com/cedriekvos/cedriekvosbe/compare/1.0.0...1.0.1
[1.0.0]: https://github.com/cedriekvos/cedriekvosbe/releases/tag/1.0.0
