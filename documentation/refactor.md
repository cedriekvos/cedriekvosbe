# Refactor backlog

Open points from the architecture review of 2026-10-20, in the recommended
order. Done so far:

| Candidate | Commits |
|---|---|
| Pest helpers split into `tests/Helpers/` by domain | `f1365c5` |
| Rendered HTML read with CSS selectors | `a6aa377`, `b432018` |
| Prose Markdown converter moved to `App\Markdown` (ADR 0009) | `f1b1d5d`, `7fe9344`, `ddd4526` |
| Slug-collision rule moved behind the Blog and Pages write repositories | `ddf44c9` |
| Pages drawn as a bounded domain in the module map | `5a93232` |

## 1. Unit tests for the Livewire admin components

**Files:** `app/Livewire/Admin/{AboutForm, MessageForm, MessageIndex, PageForm, PageIndex, PostForm, PostIndex, ScratchpadForm}.php`

**Problem:** none of the eight components has a unit test with `covers()`.
Line coverage still reads 100% because Feature tests exercise them, but
mutation testing only mutates classes a test covers, so their code is never
mutation-tested. A forced run on `PostForm` and `PageForm` leaves 18
mutations untested. The `feature-development` skill asks for one unit test
per production class.

**Proposal:** add `tests/Unit/Livewire/Admin/{Component}Test.php` with
`covers()` for each component. Test the component's own logic (validation
rules, the create and update paths, the 404 on an unknown slug) with
`Livewire::test()`.

**Decide first:** whether Livewire components count as production classes
that need a unit test, or are an exempt part of the delivery layer. If they're
exempt, record that as a rule in `.ai/rules/` so the next review doesn't raise
it again. Light track either way.

## 2. The disk-faking test helpers are misnamed

**Files:** `tests/Helpers/{Blog, Pages, Microblog}.php`, plus the 26
acceptance test files that call them

**Problem:** `usesFakePostsRepository()`, `usesFakePagesRepository()` and
`usesFakeMicroblogRepository()` fake storage disks, not repositories.
`usesFakePostsRepository()` also fakes the `meta` disk the homepage reads the
about-me section from.

**Proposal:** rename them after what they fake, for example
`usesFakePostsDisk()`, and give the homepage its own helper that fakes both
`posts` and `meta`.

**Decide first:** the rename edits files in `tests/Feature/` and
`tests/Browser/`, which CLAUDE.md asks you to approve before they change.

## 3. One shared core for Blog and Pages

**Files:** `app/Blog` and `app/Pages`. The twin modules are `DraftSlug`,
`{Post,Page}FileStorage`, `{Post,Page}Filter`, `{Post,Page}Source`,
`{Post,Page}PreviewFinder`, `{Post,Page}WriteRepository`,
`{Post,Page}GetRepository`, `{Posts,Pages}Repository`, plus the
`PostForm`/`PageForm` and `PostIndex`/`PageIndex` Livewire pairs.

**Problem:** Pages was built by copying Blog. The twins differ only in names,
disk and the shape of the attributes array, so every fix to the draft
lifecycle has to land, and be tested, twice. Candidates 3 and 4 have already
removed the converter copy and the duplicated collision check from the forms.

**Proposal:** one draftable document collection in `App\Markdown` that owns
the disk, the `draft-` prefix, draft filtering, the slug-collision check,
rename on save and preview lookup. Blog and Pages each supply a parser, a
factory and a sort order.

**Decide first:**
- This needs an ADR. It grows the shared kernel and builds on ADR 0007 and
  ADR 0009.
- `App\Markdown` would reach `Storage`, which `tests/Architecture/DomainTest.php`
  limits to `App\*\Storage`, and `NamingTest.php` lists the storage and
  repository namespaces. Both need changes you approve first.
- Do point 1 first, so the forms have unit tests before their collaborators
  move.

## 4. Collapse the pass-through repository stack

**Files:** `app/{About, Blog, Microblog, Pages, Scratchpad}/Repositories/*.php`
(18 modules) and their unit tests

**Problem:** each domain stacks a source, a read repository and a write
repository under a `…Repository` facade that only delegates. `PostsRepository`,
for example, holds seven one-line methods. One change ripples through several
modules and their unit tests.

**Proposal:** one repository per domain that owns reads and writes. The
`FileStorage` class stays as the adapter to the disk.

**Decide first:** this contradicts the house style in
`.claude/skills/feature-development/SKILL.md`, which says to "re-unify behind
a logic-free facade". Adopting it means rewriting that section of the skill.
It pays off mostly together with point 3.
