---
paths:
  - 'documentation/lessons/**'
---

# Lessons

## Lessons: one at a time, on request; snippets are verified, outdated lessons are retired
Write a new lesson only when the user asks for one, one per request, as documentation/lessons/NN-kebab-title.html. Copy the chrome and section shape of the latest lesson, add its row and concepts to index.html, and fix the prev/next nav and "NN / total" counters. Audience: experienced PHP developer, so explain why the code is shaped this way and what it costs, not the syntax. Quote real code in <pre data-source="repo/relative/path"> verbatim (whole lines, `// …` to elide); label illustrations "not in the codebase" with no data-source. `php documentation/tools/check-lessons.php` (in composer qa and qa:fast) must pass. When it fails, update the lesson if it still teaches the same thing; otherwise retire it (lesson-status "retired" plus lesson-verified-at commit, and a "No longer in the codebase" pill on the page and in the index). Never loosen the check, and do not replace it with snippets generated from source: a generated snippet changes silently while the prose explaining it goes stale. Lessons are documentation, so their conventions live here and in the lessons index, not in an ADR.
