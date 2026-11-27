---
type: Runbook
title: Runbook
description: "Procedures for the plugin: refreshing installment plans, clearing expired promotion data, and releasing a new plugin version."
covers:
  - Console/Import.php
  - Console/RemoveExpiredPromotionsCommand.php
  - Cron/RemoveExpiredPromotions.php
tags:
  - runbook
  - release
stale_after: "2027-05-27"
---

# Runbook

Commands run with `bin/magento` in a shop that has the plugin; in the website repository's local stack and on its test shops the wrapper is `d/magento`. Standard Magento cron, cache and indexing commands are Magento's own ([CLI guide](https://experienceleague.adobe.com/en/docs/commerce-operations/configuration-guide/cli/common-cli-commands)).

## Refresh the installment plans

A shop shows no installment badge until it holds plans for its market ([Installment plans](../functionality/installment-plans.md)). Three ways to run the import:

- `bin/magento leanpay:import` runs it at once. It needs Leanpay enabled at default level and an API key for the market.
- The Refresh button in Stores > Configuration > Sales > Payment Methods > Leanpay Installment price only queues the import for the next cron run. Where cron does not run, use the command.
- Cron runs the job `leanpay_sync_installment` on the schedule of the setting Installment Cron. A run that is missed while the machine is off is not made up later, so refresh by hand after starting a sleeping shop. To see that a run happened, search `var/log/cron.log` for the job name.

To check the result, count the plans per market: `select api_type, count(*) from leanpay_installment group by api_type`.

## Clear expired promotion data

`bin/magento leanpay:promotions` clears the Leanpay promotion fields of every product whose promotion end date is before today, in every store view. It runs the job `leanpay_remove_expired_promotions` once, whether or not Automatic Removal Of Promotion Data is on, so on a test shop it also erases test promotions whose end date has passed.

## Release the plugin

The last releases follow this order, read from the branch and tag history of this repository:

1. Test the change on a test shop (Luma shows `staging`) and post the result and the shop address in the Jira ticket.
2. Branch `release/<dd.mm.yyyy>` from `master` and merge each feature branch into it.
3. Raise `version` in `composer.json`.
4. Add `docs/releases/<version>.md` and a summary entry at the top of `docs/releases/README.md`. The file is named after the version.
5. Open the pull request from the release branch into `master` and merge it.
6. Tag the release on `master` with the version, with no `v` prefix, and create the GitHub release from the tag.
7. Check that the package `leanpay/payment` on [Packagist](https://packagist.org/packages/leanpay/payment) lists the new version.
8. If the Hyva add-on needs the release, release it in its own repository the same way. Its Composer file names the minimum plugin version it needs.
9. Comment in the ticket for Leanpay: the Packagist and GitHub release links, the ZIP link of the tag, and the update commands (`composer require leanpay/payment:^<version>`, then `bin/magento setup:upgrade`, `setup:di:compile`, `setup:static-content:deploy` in production mode, and `cache:flush`). Leanpay publishes the new version on its own website.

A feature branch has also been merged straight into `master` with its own version bump and release notes; the release branch is the way when approved changes from several branches ship together.

## Put a merged change on a test shop

A merge into the plugin needs a Jenkins build of the shop to deploy; a merge into a shop's own branch in the website repository deploys by itself. The steps are in the website repository's runbook ([leanpay-magento-website](https://github.com/magebitcom/leanpay-magento-website), read at commit 0310608).
