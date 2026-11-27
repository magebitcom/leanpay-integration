---
type: Functionality
title: Installment display
description: "The monthly installment badge and calculator shown under product prices, on which pages and when it appears, how the amount is chosen, and the settings that control its look."
covers:
  - Block/Installment
  - Controller/Installment
  - Helper/InstallmentHelper.php
  - Model/Config
  - Plugin/Block
  - Pricing/Price
  - etc/adminhtml/system.xml
  - view/frontend
tags:
  - catalog
  - installments
  - storefront
---

# Installment display

The badge shows what a product costs per month with Leanpay, so a shopper sees the installment before reaching checkout. It appears under the price, takes its amounts from the stored [installment plans](installment-plans.md), and makes no call to Leanpay while a page renders.

## What it does

The badge shows on:

- **Homepage**: product widgets, such as the product lists placed on the home page.
- **Category**: category and search listings.
- **Product**: the product page, and the calculator inside the Leanpay payment method at checkout, which follows this setting.

**Product page.** The badge opens a calculator with a slider over the available terms, the Installment Quick Information text and two links, More information and Check your limit. When the price is at or below the Amount Threshold, the badge shows the monthly amount for the Default Installment Count, shortened to the longest term Leanpay offers for that price. Above the threshold it shows the lowest monthly amount and the longest term. The badge follows the buyer's choices: it changes when a swatch, a dropdown option or a customizable option changes the price, and a variant with its own [promotion](promotions.md) shows that promotion's terms.

**Listings and widgets.** A price from the threshold up shows a monthly amount as "from ... / month" (the lowest one, above the threshold). A price under it shows the Under Threshold Text instead, where `%1` is replaced by the amount. Variants are resolved as in [Promotions](promotions.md).

**When the badge is missing.** It is hidden when:

- the page type is not ticked in Show installment price in following pages, or Disabled in all views is;
- the price is under Minimum Order Total or over Maximum Order Total (an empty limit means no limit);
- the stored plans hold no entry for the price, which is looked up by its nearest whole amount, or the price is zero;
- the plans have not been imported yet.

**Market details.** For Croatia the badge also shows the kuna equivalent, at a fixed conversion rate. With Enable Product Rounding on and the Romanian market, the price is rounded up to the next multiple of 5 before the lookup, so the calculator shows for every eligible product. The badge texts are written in Slovenian and the module ships Croatian, Romanian and Slovenian language packs.

## What the client can change

Stores > Configuration > Sales > Payment Methods:

- Leanpay > **Show installment price in following pages**: the pages above, at default and website level.
- Leanpay > **Minimum Order Total** and **Maximum Order Total**: the price range, shared with [checkout](paying-with-leanpay.md).
- Leanpay Installment price > **Color Theme** (Default, Orange, Light, Dark), **Use Leanpay Font** (Roboto or the store font), **Installment Widget Design** (Hyva only: Default or Narrow), **More information** and **Check your limit** (the two link targets), **Group** (which financing product's plans the badge uses when no promotion applies; its list comes from the import), **Enable Product Rounding**.
- Leanpay Installment price > Advanced > **Amount Threshold**, **Default Installment Count**, **Under Threshold Text**, **Installment Quick Information**.

Every setting in Leanpay Installment price can be set per store view.

## Before you change it

- The badge is drawn into pages that Magento caches, so a change to what it shows needs a cache flush to appear. The plan import cleans the page cache itself.
- Listing code must not run a query per product card: category promotion data and variants are loaded once per page before the badges draw.
- The Hyva add-on reads this module's settings (including the widget design) and its listing and promotion lookups. See [the system map](../architecture/README.md).
- The product page calculator and the checkout calculator share one template, so a change to one shows in the other.
