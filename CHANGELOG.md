# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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

[Unreleased]: https://github.com/cedriekvos/cedriekvosbe/compare/1.0.1...HEAD
[1.0.1]: https://github.com/cedriekvos/cedriekvosbe/compare/1.0.0...1.0.1
[1.0.0]: https://github.com/cedriekvos/cedriekvosbe/releases/tag/1.0.0
