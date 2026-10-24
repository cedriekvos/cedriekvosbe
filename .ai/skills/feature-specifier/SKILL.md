---
name: feature-specifier
description: "Use this skill whenever new or changed externally visible behavior needs a specification — step 1 of this project's full pipeline. Trigger when writing or updating a Gherkin .feature file under documentation/features/ or a Dutch leesmij under documentation/leesmij/, when a request adds or changes what a visitor or admin can see or do, or when a later step finds the spec wrong or ambiguous. Covers: the two artefacts and where they go ({domain}/{feature} naming), settling ambiguity before writing and when a question to the user is warranted, the Gherkin conventions (preamble, Background, stable scenario indices, parameters that become Pest datasets), the four-phase workflow, and when a question belongs to feature-architect instead. Does NOT cover acceptance tests (feature-to-pest), production code (feature-development) or technical decisions (feature-architect)."
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

1. **Dutch explanatory document** at `documentation/leesmij/{domain}/{feature}.md`
   — a short note that captures the *why*: an intro of one or two sentences,
   then one bullet per decision with its reason in a sentence or two. Name a
   rejected alternative only when it explains the choice. Keep it to the
   essence: no history or process narration (task numbers, "tot nu toe", earlier
   rounds), no class names or file paths, and do not restate the Gherkin. When
   the behavior changes, rewrite the note to the current state rather than
   appending a section. Write native Dutch, not translated English.
2. **Gherkin specification** at `documentation/features/{domain}/{feature}.feature`
   — formal English scenarios that pin down *what* must be true.

`{domain}` is the bounded domain (e.g. `blog/frontend`, `blog/admin`, `auth`):
list the existing domains under `documentation/features/` and reuse the one
that fits — that needs no confirmation. Only a *new* domain folder needs the
user's explicit agreement before you create it. `{feature}` is a kebab-case noun
matching the naming style of sibling files in the chosen domain.

## First step: settle ambiguity

Before writing anything, identify the dimensions the requirement leaves open —
ordering, pagination, empty states, auth, validation, error handling,
draft/published distinctions, future-dated content, etc. If the domain is
unfamiliar, read 2–3 existing `.feature` files first to learn the patterns.

Where a dimension is genuinely open — two defensible answers, and the choice
changes what the tests assert — use **AskUserQuestion** to ask up to four
focused questions, and do not invent answers. Where the request, the existing
spec, or the sibling features already settle it, settle it yourself and tell
the user which reading you took. Every question interrupts the user, so spend
them on real forks, not on confirmations.

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
4. Ask the user to approve the finished spec **when you asked them questions
   in the first place, or when you are proposing a new `{domain}` folder** —
   those calls are theirs. A spec that lands in an existing domain with
   nothing genuinely open goes straight on to the next step.

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

A new `{domain}` folder and any genuine ambiguity need the user's explicit
answer first; nothing else does. When you report on the spec, say which
questions you asked and which dimensions you settled yourself, so the user can
tell what was decided from what was assumed. Carry forward what the next step
needs: the domain and feature paths you chose, the ambiguity resolutions, and
any technical question still open for **feature-architect**.
