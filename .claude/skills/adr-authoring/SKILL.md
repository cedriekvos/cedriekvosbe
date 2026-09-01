---
name: adr-authoring
description: "Use this skill whenever an Architecture Decision Record under documentation/decisions/ is written, updated, or superseded — it owns the ADR file format and house writing style, not the decision itself. Trigger when recording a new ADR, marking one Accepted/Rejected/Superseded, adding a back-reference from an older ADR to a newer one, or updating the ADR index. Covers: the HTML page format and where the chrome comes from, the NNNN-kebab-title.html naming and sequence, the four sections (The rule · Why · Why not · Fallout), the decision-first lede, status pill classes, the index row and prev/next wiring, immutability and superseding, and the writing rules that keep an ADR short enough to be reread (title states the decision, budget the prose, cite don't paste, no process narration). Does NOT decide anything: which technical option wins is feature-architect's call, behavior is feature-specifier's, and per-feature class design is feature-development's."
metadata:
  author: cedriekvos
---

# ADR Authoring

You are writing an **Architecture Decision Record** into
`documentation/decisions/`. This skill owns the **format and the writing**. It
does not own the decision — that is the caller's (normally
**feature-architect**).

The reader you are writing for is **feature-development, looking for a
constraint** — not a historian. Everything below follows from that.

## Format: HTML pages

ADRs are **HTML pages, not Markdown**. `documentation/decisions/index.html` is
the hub — it carries the index, the conventions, and the house style, and every
new ADR needs a row added to it.

- **Path:** `documentation/decisions/NNNN-kebab-title.html`, `NNNN` being the
  next zero-padded 4-digit sequence number. `ls documentation/decisions/` to
  find it; never reuse or renumber.
- **Never hand-write the chrome.** Copy `documentation/decisions/template.html`
  and fill it in. The Tailwind CDN link, header block, status pill, section
  headings and prev/next footer are all part of it and must stay byte-identical
  across pages — read a recent ADR (0006 is a compact one, 0003 a long one) if
  you need to see the pattern in use.
- **Delete the blue instruction box** at the top of the template. It exists only
  to explain the placeholders.
- **Language:** English. The `leesmij` is Dutch; ADRs are not.
- **One decision per file.** If a page decides two things, it is two pages.

## The page

Title, then a lede blockquote, then metadata, then four sections in this order.

**Title** — states the *decision*, not the topic. "Highlight fenced code only"
beats "Wire tempest/highlight for fenced code only, not via HighlightExtension
wholesale."

**Lede** — one or two lines in the blockquote, carrying the decision **and** the
constraint that settled it. A reader who stops here must still be correctly
informed. The shape that works: *"**We do X instead of Y** — the reason."*

**Metadata** — `Driver` (the `.feature`/`leesmij` or review that prompted it)
and `Related` (ADRs this builds on, corrects, or scopes). Drop the `Related`
pair if there are none.

| Section | Holds |
|---|---|
| **The rule** | The concrete constraint the coder must honour: a snippet, a class name, a path, a forbidden call. First, because it is what the page gets reopened for. |
| **Why** | Three to six sentences — the constraint that forced a choice, and why this option satisfies it. |
| **Why not** | The rejected options, one reason each. At minimum the runner-up. Two items may be a list; from three up, use the table. |
| **Fallout** | What it makes harder, new constraints on the implementation, follow-up work, and what would trigger superseding this ADR. |

### Writing rules

- **Budget the prose: one screen, ~60 lines.** Tables, snippets and `<details>`
  blocks don't count; everything else does. Past that, something on the page is
  not a decision. A genuinely small decision gets the template's one-paragraph
  form instead.
- **Cite, don't paste.** `vendor/league/commonmark/src/…/Foo.php:42`, never a
  quoted block of vendor source — pasted vendor code goes stale silently.
- **No process narration.** Never write how you arrived at the decision: not
  which file you read, which agent flagged what, what was asked in what order,
  or what you had assumed earlier. Record what is true and what it costs.
  "Rejected by the user" earns one clause when it explains an otherwise odd
  choice; the deliberation does not.
- **Evidence longer than the decision** goes in a collapsed `<details>` block.
- **Say what is left to feature-development** rather than over-specifying the
  class graph.

## Building blocks

Copy these from `template.html`; they are reproduced here so you can check your
markup without opening it.

Status pill — swap the colour classes with the status:

```html
<span class="text-xs font-semibold px-2 py-1 rounded bg-emerald-100 text-emerald-800">Accepted</span>
```

`bg-amber-100 text-amber-800` Proposed · `bg-emerald-100 text-emerald-800`
Accepted · `bg-rose-100 text-rose-800` Rejected · `bg-slate-200 text-slate-600`
Superseded.

Inline code inside prose:

```html
<code class="font-mono text-[0.875em] bg-slate-100 text-slate-800 rounded px-1 py-0.5">PostFileParser</code>
```

Code block — the `whitespace-pre-wrap break-words` pair makes long lines wrap
instead of hiding off the right edge; drop it only for column-aligned diagrams
that must not reflow:

```html
<div class="rounded-lg bg-slate-900 overflow-hidden">
  <div class="lbl text-slate-500 px-4 pt-3">php</div>
  <pre class="overflow-x-auto px-4 py-3 text-[13px] leading-relaxed whitespace-pre-wrap break-words"><code class="font-mono text-slate-100">// escape &lt; &gt; &amp; in here</code></pre>
</div>
```

Cross-links to other ADRs, in prose:

```html
<a class="text-slate-900 underline decoration-slate-300 underline-offset-2 hover:decoration-slate-900 transition" href="0004-highlight-extension-fenced-only-wiring.html">ADR&nbsp;0004</a>
```

## Finishing: three files, not one

A new ADR is not done until all three are true.

1. **The page** exists at `NNNN-kebab-title.html`, instruction box removed.
2. **`index.html` has a row** for it — number, title, status pill, date, and a
   one-line summary of *what it decides* (not what it is about). Copy the
   preceding row and edit; keep the list in numeric order.
3. **The previous ADR's footer** gets its `Next` link pointed at the new page,
   and the new page's `Previous` link points back at it.

Then re-read your own page against the writing rules above and cut.

## Immutability and superseding

The **decision** is immutable once Accepted — reformatting, fixing a link, or
adding a back-reference to a later ADR is not a rewrite.

- Don't edit an Accepted ADR to change what was decided. Write a **new** ADR
  that supersedes it.
- When you supersede, update the old page's status pill to
  `Superseded by NNNN`, add a `Related` line pointing forward, and update its
  index row.
- When a new ADR only *corrects part* of an older one, both stay `Accepted`;
  say so in both `Related` rows (see 0003 ↔ 0004 for the pattern).
- Mark a new ADR `Accepted` only once the user has approved any dependency or
  irreversible choice it implies. Until then it is `Proposed`.

## Boundaries

- You write under `documentation/decisions/` and nowhere else.
- You do not choose the technical option, author specs, write code or tests, or
  run the test suite.
- If the question turns out to be externally visible behavior, it belongs to
  **feature-specifier**; if it is a per-feature class graph, to
  **feature-development**. Say so instead of recording an ADR.
