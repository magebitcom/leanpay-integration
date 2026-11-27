---
type: Functionality
title: Installment plans
description: "How the shop imports Leanpay's installment plans and financing products, the three ways to run the import, and what a shopper sees while plans are missing or being replaced."
covers:
  - Cron/SyncInstallments.php
  - Console/Import.php
  - Controller/Adminhtml
  - Block/System
  - etc/adminhtml
  - etc/crontab.xml
  - view/adminhtml/templates/system
tags:
  - installments
  - import
  - cron
---

# Installment plans

The installment badge and calculator need Leanpay's plans, which list the monthly amount for each loan amount and term. The shop keeps its own copy, filled by an import from Leanpay, together with the list of Leanpay's financing products that the [promotions](promotions.md) dropdowns use. Until an import has run for a market, its shops show no [badge](installment-display.md).

## What it does

The import runs once for each market (API Endpoint Type) that has an API key, asks Leanpay for that market's plans (the sandbox in Demo mode), replaces the stored plans and financing products of the market with the answer, and then cleans the full page cache. The key of the last store view that uses a market is the one sent ([Leanpay API](../integrations/leanpay-api.md)).

It does nothing when:

- Leanpay is not enabled at default level;
- a market has no API key;
- Leanpay's answer is empty or holds no plan groups, in which case the old plans stay.

The old plans of a market are deleted before the new ones are written, with no transaction around it. While an import runs, that market's badges can disappear, and an error in the middle leaves partial data until the next import. A failed request or save is logged to Magento's own logs.

There are three ways to run it:

- **Cron**, on the schedule in Installment Cron (a cron expression, at default and website level).
- **A console command**, which runs the same code at once. Use it where cron does not run, such as a local stack or a sleeping test shop; the [runbook](../operations/runbook.md) has the command.
- **Refresh**, the button in Leanpay Installment price, which queues one run for the next cron run and reports success once it is queued, even when no cron is running.

## What the client can change

Stores > Configuration > Sales > Payment Methods:

- Leanpay > **Installment Cron**: when the scheduled import runs.
- Leanpay Installment price > **Refresh**: queues an import.

The plans themselves belong to Leanpay and are overwritten on every import, so nothing in them is edited in the admin.

## Per environment

A shop in Demo mode imports Leanpay's sandbox plans, which differ from production plans. A database copied from another shop brings that shop's plans, which can be empty or for another market.

## Before you change it

- The stored plans are large, and every badge reads them, so queries on them must keep using the indexed columns (loan amount, group and market).
- The import raises events before it sends the request and before it saves the plans, so another module can adjust the request headers or the plans without changing this code.
- Replacing a market's plans must stay per market: an import for one market must not delete another market's plans.
