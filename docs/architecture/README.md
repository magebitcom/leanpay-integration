---
type: Architecture
title: System map
description: "How the Leanpay payment plugin plugs into a Magento shop, what it stores, which outside systems it talks to and how the Hyva add-on depends on it."
covers:
  - registration.php
  - etc/module.xml
  - etc/config.xml
  - etc/di.xml
  - etc/frontend
  - etc/db_schema.xml
  - etc/db_schema_whitelist.json
  - Api/Data
  - Api/InstallmentProductRepositoryInterface.php
  - Api/InstallmentRepositoryInterface.php
  - Api/SearchCriteria
  - Model/Installment.php
  - Model/InstallmentProduct.php
  - Model/InstallmentProductRepository.php
  - Model/InstallmentProductSearchResults.php
  - Model/InstallmentRepository.php
  - Model/InstallmentSearchResults.php
  - Model/ResourceModel
  - Setup/Patch
tags:
  - architecture
  - leanpay
  - magento-module
stale_after: "2027-05-27"
---

# System map

The plugin is one Magento 2 module, installed from Packagist into a merchant's shop. It lets the shop offer Leanpay installment payments at checkout and shows the monthly installment next to product prices, for Leanpay's Slovenian, Croatian and Romanian markets. It runs on the standard Luma storefront; the Hyva storefront gets its widget and checkout component from a separate add-on repository that depends on this module. The platform is Magento 2 ([developer docs](https://developer.adobe.com/commerce/php/development/)).

## The module's parts

- **Checkout.** A payment method called Leanpay, with its own checkout component and three storefront routes (token hand-over, success, failure) plus one for Leanpay's notification. [Paying with Leanpay](../functionality/paying-with-leanpay.md).
- **Catalog.** An extra price type, the installment price, drawn under product prices on the product page, in category and search listings and in product widgets, and a small route that returns the badge by Ajax. [Installment display](../functionality/installment-display.md).
- **Promotions.** Attributes on products and categories, and a global setting, that choose which Leanpay financing product a cart or badge uses. [Promotions](../functionality/promotions.md).
- **Background.** A cron job and a console command that import Leanpay's installment plans, and a second pair that clears expired promotion data. [Installment plans](../functionality/installment-plans.md).
- **Admin.** Settings under Payment Methods and a Leanpay Promos section, an extra group on the product form and the category form, and a Refresh button.

The module declares which standard Magento parts it hooks into (payment availability checks, the price box, the product view and configurable product JSON, the product list and widget blocks, CSRF validation for its own routes) in its dependency-injection files; the product-page and listing behaviour is in [Installment display](../functionality/installment-display.md), the checkout behaviour in [Paying with Leanpay](../functionality/paying-with-leanpay.md).

## What it stores

- **Installment plans.** One row per market, group, loan amount and term with the monthly amount, replaced on every import. The storefront reads them for badges and the calculator.
- **Financing products.** The list of Leanpay product groups per market, filled by the same import. Admin dropdowns for promotions read it.
- **Promotion attributes** on products and categories, valid per store view, and copied onto quote items so checkout sees them.
- **No payment records of its own.** Order, invoice and payment data are Magento's own. The only extra payment state is the Leanpay token, kept in the checkout session, and the quote id doubles as Leanpay's identifier of the order.

Data access goes through repositories with search-criteria processing, so the module's data layer follows Magento's service-contract pattern.

## Outside systems

| System | Sends to the shop | Receives from the shop | Doc |
|---|---|---|---|
| Leanpay API (sandbox and production, per market) | Payment token, hosted checkout, payment status notifications, installment plans and financing products | Token request with order data, the buyer's browser, plan requests with the API key | [Leanpay API](../integrations/leanpay-api.md) |
| Hyva add-on, repository [leanpay-hyva-compatibility](https://github.com/magebitcom/leanpay-hyva-compatibility) | The Hyva widget and Hyva Checkout payment method | Badge data, promotion terms, the widget design setting and the listing preload from this module | below |

## The Hyva add-on

The add-on is its own repository and Composer package and requires this module: its Composer file states the minimum module version it works with. It reads the settings and the promotion and plan lookups of this module and draws them with Hyva components. A change here that the add-on reads therefore needs a matching add-on release, and the [release notes](../releases/README.md) of this module say which add-on version a release needs. Change those lookups or settings compatibly, or release both.

## Request path

1. A shopper opens a page; a product page, listing or widget renders the price box, and the installment price type adds the badge from the stored plans.
2. At checkout the shopper picks Leanpay and places the order; the module asks Leanpay for a token and posts the shopper to Leanpay's hosted checkout.
3. The shopper returns to the success or failure route; later Leanpay's notification reaches the notification route and the order is invoiced or canceled.
4. Cron imports the plans on a schedule set in the admin, and clears expired promotions when that is switched on.
