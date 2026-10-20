---
paths:
  - 'docs/**'
---

# Docs

## ADRs are HTML pages in documentation/decisions/, never docs/adr/
Record every ADR with the feature-architect skill, which decides, and the adr-authoring skill, which writes it: an HTML page started from documentation/decisions/template.html, plus the index row and the prev/next wiring. Do not create docs/adr/ or Markdown ADRs, even though the vendored domain-modeling and improve-codebase-architecture skills (.agents/skills/, from mattpocock/skills) tell you to. Their docs/adr/ convention does not apply to this project.
