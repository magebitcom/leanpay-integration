---
type: Functionality
title: Paying with Leanpay
description: "How a buyer pays in installments through Leanpay at checkout, how the order is confirmed or canceled, when the method is offered, and the market and environment settings behind it."
covers:
  - Controller/AbstractAction.php
  - Controller/Checkout
  - Model/Method
  - Model/Config/Source/ApiEndpointType.php
  - Model/Config/Source/Modes.php
  - Plugin/CanUseForCountryPlugin.php
  - Plugin/TotalMinMaxPlugin.php
  - Block/LeanpayConfig.php
  - Block/Adminhtml
  - Helper/Data.php
  - etc/adminhtml/system.xml
  - view/frontend/layout/checkout_index_index.xml
  - view/frontend/layout/leanpay_checkout_success.xml
  - view/frontend/templates/config.phtml
  - view/frontend/web/js/payment
  - view/frontend/web/template/payment
tags:
  - checkout
  - payment
  - leanpay
---

# Paying with Leanpay

Leanpay lets a buyer split an order into installments. The shop hands the order to Leanpay, the buyer finishes the purchase on Leanpay's hosted page, and Leanpay later tells the shop whether it was paid. The method works for the Slovenian, Croatian and Romanian markets and on the Luma checkout; Hyva Checkout gets its component from the [add-on](../architecture/README.md).

## What it does

**Placing the order.** The buyer picks Leanpay (shown with its logo, the instructions text and an installment calculator for the cart total) and places the order. At that moment the shop asks Leanpay for a token with the order total, the buyer's name, billing address and phone, the priced cart lines (name, SKU, unit price, quantity) and, when a [promotion](promotions.md) applies, its financing product. Leanpay identifies the order by the quote id. With a token the order stays new and the buyer's browser is posted to Leanpay's checkout. Without one the order is canceled and the buyer normally sees an error (the exception is in Before you change it).

**Amounts.** With RON-Only Mode on and the Romanian market, the order total and line prices go to Leanpay as they are, in RON, without conversion. In every other case the line prices are converted to euro through the shop's currency rates, so the shop needs a rate to euro; the admin shows whether one exists in Base Currency rate.

**Coming back.** The success route shows the order success page and changes nothing on the order. The failure route cancels the order if it is still new, restores the cart and sends the buyer to the Checkout URL to redirect after failure, a Magento path or a full URL.

**Confirmation.** Leanpay posts a status notification to the shop, to an address set on Leanpay's side. The shop accepts it only when its signature, computed with the API Secret Word, matches:

- Success: an invoice is created, the invoice email is sent and the order moves to processing. An order that cannot be invoiced, for example one already invoiced, is left as it is and the reason is logged.
- Failed, canceled or expired: the order is canceled.
- Any other status, such as pending, changes nothing.

The order is found by quote id among orders that are not canceled or closed. Success notifications can take hours to arrive, so a paid order may wait in its first state ([Leanpay's documentation](https://docs.leanpay.com/leanpay-english/api-integration/api-integracija/) has the delivery times).

**Refunds.** The shop sends nothing to Leanpay when a credit memo is made: the invoice is captured offline and no refund call exists.

## When the method is offered

- Enable is on, set at default or website level.
- The shipping country is allowed by Payment from Applicable Countries and Payment from Specific Countries.
- The billing country is allowed by Payment from Applicable Billing Countries and Payment from Specific Billing Countries.
- The cart total is inside Minimum Order Total and Maximum Order Total; an empty limit means no limit.
- The order is placed by a buyer: the method cannot be used for orders created in the admin.

## What the client can change

Stores > Configuration > Sales > Payment Methods > Leanpay, set in the admin:

- **Enable**, **Title**, **Instructions**, **Sort Order**: how the method appears.
- **API Endpoint Type** (SLO, CRO or RON): the market, per store view. It picks Leanpay's host, see [Leanpay API](../integrations/leanpay-api.md).
- **RON-Only Mode**: shown only for RON; skips the euro conversion above.
- **Environment Mode**: Demo uses Leanpay's sandbox, Production the live service. Default and website level.
- **API Key** and **API Secret Word**: Leanpay's credentials for the vendor account, stored encrypted, at default, website or store view level. A store view uses its own pair, which is how several markets run in one shop.
- **Checkout URL to redirect after failure**, the country lists and **Minimum Order Total** and **Maximum Order Total** above.
- **Base Currency rate**: a read-only display of the rate to euro.

## Per environment

Demo mode sends orders and plan requests to Leanpay's sandbox with the sandbox key. The two Magebit test shops run in Demo mode (read in the [website repository](https://github.com/magebitcom/leanpay-magento-website) at 0310608); see [developing the plugin](../operations/local-setup.md).

## Before you change it

- The quote id is Leanpay's order identifier. Shops that share one sandbox account and start from copied databases can send the same quote id, and Leanpay then returns no token. A fresh cart gets a new quote id.
- The token of an earlier attempt stays in the checkout session when a later token request fails, so the buyer can be sent to Leanpay with the old token while the new order is canceled.
- The notification route skips Magento's form key check, because Leanpay cannot send one; the signature check is what protects it, so keep that check intact.
- A notification with a wrong signature is logged and answered with an empty success, so Leanpay stops retrying: a wrong API Secret Word leaves paid orders waiting. One for an order that is not found, already canceled for example, ends in an error response instead.
