---
type: Integration
title: Leanpay API
description: "The Leanpay endpoints the plugin calls and the notification it receives: what goes out and comes in, who owns which data, and what happens when a call fails."
covers:
  - Api/RequestInterface.php
  - Model/Request.php
  - Cron/SyncInstallments.php
  - Helper/Data.php
  - Plugin/CsrfValidatorSkip.php
  - Logger/Handler.php
  - Logger/InstallmentSync.php
  - Logger/PaymentLogger.php
  - etc/csp_whitelist.xml
tags:
  - integration
  - leanpay
  - payment
stale_after: "2027-05-27"
---

# Leanpay API

Leanpay is a payment provider that offers installments through its own hosted checkout. The plugin lets a shop sell through Leanpay's vendor API: the shop asks Leanpay for a payment token, the buyer pays on Leanpay's page, and Leanpay reports the outcome. Leanpay runs the API, its sandbox and the vendor accounts, and sets the shop's notification address on its own side.

## What flows

| Direction | What | When |
|---|---|---|
| Shop to Leanpay | Token request: API key, quote id as the transaction id, order total, buyer name, billing address and phone, priced cart lines, optional financing product code, and the shop's success and failure addresses | When a buyer places an order |
| Buyer's browser to Leanpay | A form post with the token to Leanpay's checkout | Right after the order |
| Leanpay to shop | Status notification: Leanpay's transaction id, the quote id, amount, status and a signature | When the outcome is known, which can take hours |
| Shop to Leanpay | Plan request with the API key; the answer lists financing products with loan amounts, terms and monthly amounts | On the plan import |

Hosts per market, with the market set by API Endpoint Type and sandbox or live set by Environment Mode:

| Market | Demo (sandbox) | Production |
|---|---|---|
| SLO | stage-app.leanpay.si | app.leanpay.si |
| CRO | lapp.leanpay.hr | app.leanpay.hr |
| RON | stage-app.leanpay.ro | app.leanpay.ro |

The token request goes to `/vendor/token`, the checkout form to `/vendor/checkout`, the plan request to `/vendor/installment-plans` on the market's host. Leanpay's notification arrives at the shop's `/leanpay/checkout/api` route.

## Who owns which data

- **Leanpay** owns payment outcomes, the financing products and their plans, and the vendor account with its API key and secret word. The shop's copy of plans and financing products is replaced on every import, so an edit is never kept.
- **The shop** owns the order, its invoice and its state. The invoice and the confirmation of payment come from a signed notification.

## How it connects

The API key goes in the body of the token and plan requests and identifies the vendor; there is no other login. Notifications carry an MD5 signature built from the transaction ids, the MD5 of the API Secret Word, the amount to two decimals and the status; the shop recomputes it and ignores a notification that differs. Leanpay's documentation has the exact recipe. The browser form post is allowed by a content security policy that lists the Leanpay hosts above and Leanpay's checkout hosts, so a new host Leanpay redirects to must be added to that list or the browser blocks the post.

For a market with several store views, the plan import uses the key of the last store view that has the market; payments use the key of the store view they are placed in.

## When it stops

- **Token request.** It waits 30 seconds. A connection error is logged to the payment log and the order is canceled. An answer without a token, such as an HTTP error body, cancels the order without a log line. See [Paying with Leanpay](../functionality/paying-with-leanpay.md).
- **Notification.** A wrong signature is logged and answered with an empty success, so Leanpay stops retrying. An order that is not found ends in an error response, and, by Leanpay's documentation, Leanpay posts again until it gets a success, up to ten posts. If the shop is down for all of them, the order stays unconfirmed.
- **Plan import.** An error is logged to Magento's own logs and the market keeps its old plans, unless it fails after the old ones were deleted ([Installment plans](../functionality/installment-plans.md)).

Errors show in the logs described in [troubleshooting](../operations/troubleshooting.md).

## Per environment

Demo mode talks to the sandbox hosts with the sandbox key, Production to the live hosts. A sandbox account is shared by every shop given its key, which matters for [quote id clashes](../functionality/paying-with-leanpay.md).

## Before you change it

- Leanpay matches the notification to the order by the transaction id the shop sent, the quote id. Do not send another identifier without changing how notifications find orders.
- The signature recipe and the return addresses are Leanpay's contract; a change there needs Leanpay's agreement first.

## Its docs

[Leanpay API integration guide](https://docs.leanpay.com/leanpay-english/api-integration/api-integracija/) (checkout request, status response, signature and retries). It does not describe the plan request.
