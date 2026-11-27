---
type: Testing
title: Testing
description: "What checks protect the Leanpay plugin: no automated tests in this repository, manual testing on the test shops with Leanpay's approval, and the acceptance suite in the website repository."
covers:
  - .github/workflows/docs-health.yml
tags:
  - testing
  - qa
stale_after: "2027-05-27"
---

# Testing

The plugin handles a buyer's payment and the installment terms Leanpay shows, so a regression costs orders or advertises wrong terms. This repository has no unit, integration or end-to-end tests and no code-style check. A change is tried by hand on the test shops before release, and Leanpay's reviewer checks it there too.

## Layers

| Layer | Runs | Blocks a merge | More |
|---|---|---|---|
| Manual test on a test shop: pay with Leanpay in the sandbox, check the badge and the promotion terms | By hand, after the change is merged into the branch a shop tracks and the shop is built | No | Below |
| Acceptance suite for the Leanpay checkout and promotion terms, in the website repository | By hand on the local stack | No | Below |
| Docs check (changed code whose doc was not updated) | On pull requests into `staging` and `master`, and by hand with `docs/.health-checker/run` | Only if the branch rules require the `docs-health` check | [Developing the plugin](../operations/local-setup.md) |

## Manual testing on the test shops

The Magebit test shops belong to the [website repository](https://github.com/magebitcom/leanpay-magento-website): the Luma shop runs the plugin's `staging` branch and the Hyva shop runs `master` (read at commit 0310608), so an unreleased change can be shown on the Luma shop only. After the shop is built, the developer posts the result and the shop address in the Jira ticket, and Leanpay's reviewer checks on the same shop. The release notes of each version list the manual cases it needs under Testing Recommendations; use them as the checklist.

The sandbox needs Demo mode and sandbox keys. Leanpay's sandbox notification is what moves a test order to processing, so a test order can wait for it ([Paying with Leanpay](../functionality/paying-with-leanpay.md)).

## The acceptance suite

The suite lives in the website repository, not here. It buys test products through the Luma storefront, opens Leanpay's sandbox checkout and reads the installment terms. It is not maintained, does not pass and is not run by any pipeline; it does not fit the Hyva storefront. Do not count on it for a change. Its own page in the website repository's docs describes how it runs (read at commit 0310608).

## Reasons

- Plugin behaviour that matters most (promotion resolution, the notification handling, the badge amounts) has no automated check, so a change to those rules is checked by reading the [promotions](../functionality/promotions.md) and [checkout](../functionality/paying-with-leanpay.md) docs and testing each rule by hand.
- No code-style or static-analysis config exists in this repository.
