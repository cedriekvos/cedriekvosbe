---
name: release
description: "Use this skill whenever the user asks for a new release or version of this project. Trigger on requests like \"make a release\", \"tag a new version\" or \"release this\", and at the end of the security-update skill when the user asked for a release. Covers: checking that everything to ship is committed, the full composer qa gate on the released commit, choosing a patch or minor version, writing the CHANGELOG.md entry in the house style from the commits since the last tag, the version file, the release commit, the tag, and handing the push to the user. Does NOT cover updating dependencies (security-update), committing the work being released (git-commit), or pushing."
metadata:
  author: cedriekvos
---

# Release

A release is one commit, `chore(release): version X.Y.Z`, that adds an entry
to `CHANGELOG.md` and writes the number to `version`, plus a tag on that
commit. Make one only when the user asks for it. Never push: the Buddy deploy
runs on every push to `main`, so the push is the user's call.

## 1. Check what ships

- Everything the release describes is already committed. Commit outstanding
  work with the `git-commit` skill first, if the user asked for that, or stop
  and ask. Untracked files that are not part of the project stay untracked.
- You are on `main`. The last release is the highest tag:
  `git tag --sort=-v:refname | head -1`.
- Read every commit since that tag, bodies included; they are the source for
  the entry: `git log --reverse --format='=== %h %s%n%b' <last tag>..HEAD`.
  Also take over anything already listed under `## [Unreleased]`.

## 2. Run the full gate once

Run plain `composer qa` on the commit you release, in the background with its
output in a log file, and kill `playwright run-server` when it finishes. It is
the gate the deploy runs. `qa:fast` passed on both broken deploys of 1.7.8 and
1.7.9, because its Rector and PHPStan runs are cached and `--tia` does not see
`vendor/`. Skip it only when it already passed on this exact tree in this
session. If it fails, fix the cause in its own commit before you write the
entry.

## 3. Pick the number

Semantic versioning, as the history uses it:

- **Minor** (`1.8.0`) when a visitor or admin can see the change: a `feat`
  commit, or a changed `.feature` spec. 1.6.0 added a header link; 1.7.0
  restyled a logo.
- **Patch** (`1.7.11`) for everything else: security and dependency updates,
  fixes, refactors, documentation, QA and agent-workflow changes.
- **Major**: never on your own. Ask the user.

Tags have no `v` prefix.

## 4. Write the entry

Insert it under `## [Unreleased]`, which stays as an empty heading:

```markdown
## [1.7.11] - 2026-11-21

One to three sentences: what this release does, written for someone who knows
the project but not the commits. End with "The site itself is unchanged."
when no visitor or admin can see a difference.

### Security
```

- Headings in this order, each only when it has an item: `### Security`,
  `### Added`, `### Changed`, `### Deprecated`, `### Removed`, `### Fixed`.
- Each item is `- **Area** — what changed and why.` Reuse the areas the file
  already uses: Documentation, Workflow, Dependencies, Composer, QA, Tests,
  Admin, Navigation, Deployment. A new `.ai/rules` file is an item that starts
  with its path instead of an area.
- A security item names the package and both versions, then the advisory with
  its ID and severity: `- **Laravel** 13.38.0 to 13.43.0, clearing
  CVE-2026-128083 (GHSA-63hm-g96r-2m2q, low): …`. Say in a sentence what the
  flaw allows, then whether this site can reach it and why. The security-update
  skill hands you that verdict.
- Describe the effect, not the commits: no hashes, no commit types, no
  "this commit". Combine commits that belong together into one item.
- Leave out what never reaches git, such as the gitignored `CLAUDE.md` and
  the skills Boost generates.
- Wrap prose at 78 columns, as the rest of the file does.

At the bottom, point `[Unreleased]` at the new tag and add the new link above
the previous one:

```markdown
[Unreleased]: https://github.com/cedriekvos/cedriekvosbe/compare/1.7.11...HEAD
[1.7.11]: https://github.com/cedriekvos/cedriekvosbe/compare/1.7.10...1.7.11
```

Write the bare number, with a trailing newline, to `version`.

## 5. Commit and tag

Use the `git-commit` skill. Stage `CHANGELOG.md` and `version` only and commit
them as `chore(release): version X.Y.Z`, with no body. Then tag that commit
without annotation, as every earlier tag is: `git tag X.Y.Z`.

## Before you hand over

Report the version, the commits and the tag, and say whether `composer qa`
passed. Then ask whether to push both with `git push origin main X.Y.Z`;
pushing `main` starts the production deploy. If the release turns out wrong
before it is pushed, ask before you move the tag or change the commit.
