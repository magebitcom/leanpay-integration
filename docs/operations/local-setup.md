---
type: Local Setup
title: Developing the plugin
description: "How to develop and try the plugin, which has no shop of its own: through the website repository's submodule, and what runs on a commit and a pull request."
covers:
  - .github/workflows/docs-health.yml
tags:
  - local-setup
  - development
stale_after: "2027-05-27"
---

# Developing the plugin

This repository is only the Magento module: there is no shop, no container stack and no tests here. A developer runs it inside the Leanpay website repository, [leanpay-magento-website](https://github.com/magebitcom/leanpay-magento-website), which holds the Magebit test shops and the local Docker stack, and which includes this repository as a git submodule at `app/code/Leanpay/Payment`. On the Hyva shop branches the add-on [leanpay-hyva-compatibility](https://github.com/magebitcom/leanpay-hyva-compatibility) sits next to it as `app/code/Leanpay/PaymentHyva`.

## What you need first

- Access to the three GitHub repositories above (organization `magebitcom`).
- The website repository's local stack, set up as its own local setup doc says (read at commit 0310608).

## Working on a change

1. Check out the website branch of the shop you will test on (`m249` for Luma, `hyva-m249` for Hyva) with its submodules, and run `git submodule update --remote`, which brings the plugin to the branch that shop tracks.
2. Run git for the plugin inside `app/code/Leanpay/Payment`, not in the website root. Branch `feature/LMM-<n>` from `master` there ([AGENTS.md](../../AGENTS.md) has the branch and pull request rules).
3. Try the change on the local shop. After a schema, dependency-injection or layout change run the usual Magento upgrade, compile and cache commands (the [CLI guide](https://experienceleague.adobe.com/en/docs/commerce-operations/configuration-guide/cli/common-cli-commands)).
4. Merge the pull request into `staging`. The Luma test shop runs `staging`; the Hyva test shop runs `master`, so an unreleased change can be shown on the Luma shop only. How to put a merged change on a test shop is in the website repository's runbook (read at commit 0310608): a merge into the plugin needs a Jenkins build of the shop.

## Hooks

There is no commit hook and no hook manager. On a pull request into `staging` or `master`, the PR gate (`.github/workflows/docs-health.yml`) runs the docs check; it blocks the merge only where the branch rules require the check. Run it by hand before pushing with `docs/.health-checker/run` (it needs Node 12 or newer on the host). The guide for keeping the docs up to date is [MAINTAINING.md](../MAINTAINING.md).
