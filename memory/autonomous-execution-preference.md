---
name: autonomous-execution-preference
description: How this user wants Claude to work — autonomously, without confirmation prompts
metadata:
  type: feedback
---

The user prefers autonomous execution: run in bypass/accept-edits permission mode and do NOT ask for confirmation on routine steps. They said "i want the bypass mode on dont ask me anything."

**Why:** They want momentum on multi-step builds rather than per-step approval.

**How to apply:** Make sensible default decisions and proceed. Reserve [[AskUserQuestion]] for genuinely blocking, irreversible choices that change the outcome — not routine implementation steps. Still surface outcomes faithfully (test failures, skipped steps).
