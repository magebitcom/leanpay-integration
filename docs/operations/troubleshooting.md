---
type: Troubleshooting
title: Troubleshooting
description: "Symptoms that come from this plugin's own behaviour, each with its cause and fix."
covers:
  - Logger/Handler.php
tags:
  - troubleshooting
  - leanpay
stale_after: "2027-05-27"
---

# Troubleshooting

The plugin's payment log is `var/log/leanpay.log`. Plan import errors go to Magento's own logs in `var/log`, not to that file. One entry per symptom.

## Checkout does not reach Leanpay and the order is canceled

Leanpay returned no token. The plugin sends the quote id as Leanpay's transaction id, so two shops that share one sandbox account and start from copied databases can send the same id, and Leanpay then answers without a token. The plugin cancels the order and logs nothing for an answer without a token. Retry with a fresh cart, which has a new quote id. The behaviour is in [Paying with Leanpay](../functionality/paying-with-leanpay.md).

## A buyer is sent to Leanpay after a failed attempt

The token of an earlier attempt stays in the checkout session and is reused when a later token request fails, while the new order is canceled. Retry with a fresh cart or a new session.

## A paid order stays pending

The order moves on only when Leanpay's signed notification arrives. Look in `var/log/leanpay.log` for a rejected signature first: the API Secret Word of that store view must be the one of the vendor account, and the wrong one makes every notification fail the check and be ignored. Success notifications can also take hours to arrive. If the log shows nothing, Leanpay may not have the shop's notification address, which Leanpay sets on its side (`/leanpay/checkout/api` on the shop's address).

## No installment badge or calculator

The stored plans are empty or belong to another market. Run the import as the [runbook](runbook.md) says, then check that Leanpay is enabled at default level and the market has an API key.

## The Refresh button reports success but nothing changes

The button only queues the import for the next cron run. Where no cron runs, such as a local stack or a sleeping test VM, nothing picks it up. Run `bin/magento leanpay:import` instead.

## The same financing program is listed twice

The import replaces only the rows of the market it fetched, so rows it never touches stay. The fix the team has used: empty both plan tables (`leanpay_installment` and `leanpay_products`) and run the import again. Enable Leanpay with valid API keys first, otherwise the tables stay empty.
