# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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

[Unreleased]: https://github.com/cedriekvos/cedriekvosbe/compare/1.2.0...HEAD
[1.2.0]: https://github.com/cedriekvos/cedriekvosbe/compare/1.1.1...1.2.0
[1.1.1]: https://github.com/cedriekvos/cedriekvosbe/compare/1.1.0...1.1.1
[1.1.0]: https://github.com/cedriekvos/cedriekvosbe/compare/1.0.1...1.1.0
[1.0.1]: https://github.com/cedriekvos/cedriekvosbe/compare/1.0.0...1.0.1
[1.0.0]: https://github.com/cedriekvos/cedriekvosbe/releases/tag/1.0.0
