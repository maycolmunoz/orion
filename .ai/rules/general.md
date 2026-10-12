---
paths:
  - '**/*.md, **/*.php'
---

# General

## Comments and docs are English
Write code comments, docblocks, README files and .ai/rules in English. Spanish belongs only in `resources/lang/es/**` and the published `lang/**` translation catalogs. No linter catches a Spanish comment in an English codebase — Pint checks formatting, not language — so it slips through review unnoticed.
