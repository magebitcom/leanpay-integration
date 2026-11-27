---
type: Guide
title: Maintaining these docs
description: "How to keep docs/ true when you change the code, with or without the write-project-docs skill."
---

# Maintaining these docs

The docs under `docs/` say what this project does and why, for the people and AI agents who work on it. A check keeps them in step with the code: a change to code a doc covers asks for that doc to be updated or confirmed on the same branch. If you have the `write-project-docs` skill, use it: it does all of the below. Otherwise, follow this page. This page is the same on every project and is replaced on upgrade, so do not edit it here.

## When

On every branch that changes code, before its pull request merges. Run the check yourself, from the repository root:

```sh
docs/.health-checker/run
```

The same check runs on each commit where the commit hook is installed, and on each pull request.

## What the check asks for

- **`doc.untouched`:** you changed code a doc covers, and the doc did not change. Read the doc next to your diff:
  - a sentence your change makes false: rewrite it;
  - something new a reader needs, such as a new rule, setting or outside call: add it;
  - the doc still holds: confirm it, as below. Never edit a doc only to make the check pass.
- **`review.stale`:** the doc was updated or confirmed earlier on the branch, and its code changed again after that. Read it again in the same way.
- **`covers.unclaimed`:** a new folder or file is described by no doc. Add it to the `covers` of the doc whose subject it is, or write a new doc for it (The frontmatter, below).
- **`covers.missing`:** a doc's `covers` names a path that no longer exists. Remove it, or change it to the new path.
- **`link.broken`:** a link points to a file that is not in the repository. Fix the link.
- **`doc.stale`** (a warning): the doc's `stale_after` date has passed. Check the doc against the real system it describes, fix what changed, then move the date (below).
- **`require.unmatched`** (a warning): an entry in the check's settings matches no path. Fix or remove it (Do not, below).
- **Frontmatter and date errors:** fix the field the message names (below).

**Confirming a doc.** When you have read a doc and it still holds, add a line to the commit message:

```
Docs-Reviewed: docs/<path of the doc>.md
```

Add it only after you have read the doc against your change. When the code is already committed, the line goes on an empty commit after it: the check prints that command, so run it as printed. The line confirms the doc for the whole branch, so keep it when you squash.

## Where a fact goes

`AGENTS.md` has a table of where each kind of fact goes. In short: a business rule or something the client can change goes to its area doc under `docs/functionality/`; what an outside system sends, receives or owns to its doc under `docs/integrations/`; a decision and its reason to `docs/decisions/`; a procedure or a local setup step to `docs/operations/`.

One fact lives in one doc; every other doc links to it.

## What a doc says

A sentence earns its place when it says one of these:

- what the project adds and why: what a custom feature does, for whom, and the business need behind it;
- outside knowledge: what an outside system does, which system owns which data, why something was done;
- how to build, run and release the project, in `docs/operations/` only.

Write behaviour, not wiring: what a change causes for a shopper, an admin or an outside system, not how the code is built. Name a setting by where it is in the admin and its label. Plain words, short sentences; a rule with several conditions is a list.

Never in a doc:

- a setting's value; a version; a size or limit of a container or server;
- a configuration path, or a file, class, module, table or template name. Only a doc under `docs/operations/` may name the commands, file paths, logs, services and setting paths a step needs, never their values;
- a ticket key, a commit, a date, or a pending item ("waiting for", "planned");
- a change told as a change ("now", "no longer", "changed from"), or the story of something that happened once;
- a secret, a credential, personal data, or a security weakness. Report a weakness to the project team instead.

An area doc stays at about 800 words. A new feature large enough for its own section gets its own doc, linked from the area doc.

## The frontmatter

Every file under `docs/` starts with a frontmatter block:

```yaml
---
type: Functionality
title: Gift cards
description: "Who can buy and use gift cards, and what happens to a balance on a refund."
covers:
  - path/to/the/code/it/describes
---
```

- `type` is required; keep the kind the doc already has. A new doc takes the `type` of the docs in its folder, and is linked from where they are linked, such as the folder's `README.md` or `AGENTS.md`.
- `title` and `description` are how people and agents find a doc: the description says what the doc covers in one line, in quotes.
- `covers` lists the code the doc is responsible for, folders or files. Keep it to what the doc really describes.
- `stale_after: "YYYY-MM-DD"` on docs about environments, infrastructure and outside systems: when you have checked the doc against the real system, set it six months ahead, or 30 days ahead while the project has no production yet. Never move it without checking.
- `unconfirmed: true` marks a doc with a sentence written with "likely". When you confirm or correct the last such sentence, remove the word and the field.

## Do not

- Edit this page or the check's code under `docs/.health-checker/`: both are replaced on upgrade. Its settings, `docs/.health-checker/health-checker.yaml`, are the project's and stay: fix an entry `require.unmatched` names there, and add to `ignore` only code no doc should describe, such as third-party or generated code.
- Delete a decision under `docs/decisions/` while what it decides is still in the code. When a change you mean to make breaks its rule, edit the decision in place, keep its file name and add one line of Context on why it changed; say so in the pull request when it relaxes a security or privacy rule. When you did not mean to break it, change your code so the decision holds.
- Skip the commit hook to get past a failing check; the empty review commit the check prints is the one exception. If you cannot update the docs, leave them to the project team and say so in the pull request.
