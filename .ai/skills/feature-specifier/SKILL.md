---
name: feature-specifier
description: "Use this skill whenever new or changed externally visible behavior needs a specification — step 1 of this project's full pipeline. Trigger when writing or updating a Gherkin .feature file under documentation/specifications/features/ or a Dutch leesmij under documentation/specifications/leesmij/, when a request adds or changes what a visitor or admin can see or do, or when a later step finds the spec wrong or ambiguous. Covers: the two artefacts and where they go ({domain}/{feature} naming), the grilling interview that settles every decision with the user before anything is written, the Gherkin conventions (preamble, Background, stable scenario indices, parameters that become Pest datasets), the four-phase workflow, and when a question belongs to feature-architect instead. Does NOT cover acceptance tests (feature-to-pest), production code (feature-development) or technical decisions (feature-architect)."
metadata:
  author: cedriekvos
---

# Feature Specifier

You are writing the **specification** for a piece of externally visible
behavior: acceptance criteria, scenarios, and examples. This step produces
**specifications only** — finish it before any test or production code is
written, and don't touch `app/` or `tests/` while you are in it.

## Outputs

For each feature, produce two complementary artefacts:

1. **Dutch explanatory document** at `documentation/specifications/leesmij/{domain}/{feature}.md`
   — a short note that captures the *why*: an intro of one or two sentences,
   then one bullet per decision with its reason in a sentence or two. Name a
   rejected alternative only when it explains the choice. Keep it to the
   essence: no history or process narration (task numbers, "tot nu toe", earlier
   rounds), no class names or file paths, and do not restate the Gherkin. When
   the behavior changes, rewrite the note to the current state rather than
   appending a section. Write native Dutch, not translated English.
2. **Gherkin specification** at `documentation/specifications/features/{domain}/{feature}.feature`
   — formal English scenarios that pin down *what* must be true.

`{domain}` is the bounded domain (e.g. `blog/frontend`, `blog/admin`, `auth`):
list the existing domains under `documentation/specifications/features/` and
recommend the one that fits in the interview below; a *new* domain folder is
proposed there like any other decision. `{feature}` is a kebab-case noun
matching the naming style of sibling files in the chosen domain.

## First step: the grilling interview

Before writing anything, **invoke the `grilling` skill** and interview the
user until every decision the spec depends on is settled. Follow it in full:
rounds over the design tree, every question with your recommended answer, and
no writing until the frontier is empty and the user confirms you share an
understanding. Every decision goes to the user, including the ones you could
have settled yourself. How the interview applies to a spec:

- **The design tree is the behavior.** Map the dimensions the requirement
  leaves open — ordering, pagination, empty states, auth, validation, error
  handling, draft/published distinctions, future-dated content, etc. — and the
  decisions that hang off each one. The `{domain}` and `{feature}` names are
  questions in the first round.
- **Recommend what already exists.** Before the first round, read the existing
  spec if there is one and 2–3 sibling `.feature` files in the domain. Where
  they or the request already answer a question, make that your recommended
  answer and say where it comes from, so a "yes" confirms it.
- **Leave technical branches to feature-architect.** A branch that turns on a
  cross-cutting technical decision (new dependency, file/serialization format,
  how a new bounded domain is stored) is not the user's to settle here: name it
  as open and carry it forward (see *Boundaries*).
- **Keep the reasons.** When the user explains an answer, note why: each
  decision and its reason becomes a bullet in the `leesmij`.

## Gherkin conventions

- Standard English Gherkin with the `In order to / As a / I want` preamble.
- Move repeated setup into `Background`.
- Name each scenario with the feature name and a stable index, and include that
  scenario name in a comment immediately preceding it.
- Use Gherkin parameters for any field a scenario actually asserts — they
  become Pest datasets and mutation coverage downstream. An `Examples:` table
  belongs only to a `Scenario Outline:`; a data table belongs to a plain
  `Scenario:`. They are not interchangeable.

## Four-phase workflow

1. Write the Gherkin plus the Dutch `leesmij`.
2. Prune parameters that don't improve mutation coverage of the behavior.
3. Move repeated scenario setup into `Background` where it preserves meaning.
4. Ask the user to approve the finished spec. The interview settled the
   decisions; this checks that the Gherkin and the `leesmij` say what was
   agreed.

## Boundaries — escalate instead of guessing

- If the requirement only changes existing code without changing behavior
  ("make this faster"), stop — this is not a spec task; it belongs on the
  light track.
- If the requirement hinges on a cross-cutting **technical** decision (new
  dependency, file/serialization format, how a new bounded domain is stored),
  that's the **feature-architect** skill's call — don't bake the choice into
  the spec. Name the open question and settle it there before the acceptance
  tests are written.
- Don't write tests or code as part of this step, and don't run the test suite
  or quality tools — there is nothing here for them to check yet.

## Before you move on

The spec moves on only once the interview has ended and the user has approved
the finished spec. When you report on it, list the decisions the interview
settled and the answers given. Carry forward what the next step needs: the
domain and feature paths, those decisions, and any technical question still
open for **feature-architect**.
