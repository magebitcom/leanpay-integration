---
type: Functionality
title: Promotions
description: "How a merchant attaches a Leanpay financing product to products, categories or whole carts, how conflicts are settled, and how expired promotion data is cleared."
covers:
  - Helper/Data.php
  - Model/Attribute
  - Model/Config/Backend
  - Model/Config/Source/ExclusiveOrInclusive.php
  - Model/Config/Source/PromotionDataRemovalOptions.php
  - Model/Config/Source/VendorAttribute.php
  - Model/Config/Source/VendorProductCode.php
  - Block/Adminhtml/Form/Field/DatePicker.php
  - Block/System/Config/ReadOnlySelect.php
  - Console/RemoveExpiredPromotionsCommand.php
  - Cron/RemoveExpiredPromotions.php
  - Setup/Patch/Data/AddMFPsAttributes.php
  - etc/adminhtml/system.xml
  - etc/catalog_attributes.xml
  - etc/crontab.xml
  - view/adminhtml
tags:
  - promotions
  - financing-product
  - catalog
---

# Promotions

Leanpay offers merchants special installment programs, such as zero interest, called financing products. A merchant uses promotions to say which products, categories or carts get which program. The plugin sends the chosen program's code to Leanpay when the order is placed and shows the same terms on the installment badge, so a buyer sees what Leanpay will offer. Without a promotion the order and the badge use the default financing product, set by Group in [Installment display](installment-display.md).

## What it does

A **financing product** is one of Leanpay's program names with its code. The list comes from the [plan import](installment-plans.md), so the dropdowns are empty until an import has run.

A cart gets its financing product from the first level that yields one:

1. **Product.** The Leanpay Promotions group on the product form. For a configurable product the purchased variant's own promotion wins over the parent's.
2. **Category.** The Leanpay Promotions section of the category form, from every category the cart's products are in.
3. **Whole cart.** Leanpay Promos > Multiple Financing Products.

Fields on a product or category: the financing product (Leanpay Product Code / vendorProductCode on products, VendorProductCode on categories), Inclusive Or Exclusive, Priority, Time-based, and Date If Time-based From and To. Values are per store view.

Rules:

- A product's code is ignored when the imported list holds that code for another market than the store view's API Endpoint Type.
- With Time-based on, the promotion runs from the start date through the whole end day, in the store's time zone. Time-based on with no end date never applies. With it off, the promotion always applies.
- Inclusive applies whatever else is in the cart. Exclusive applies only when nothing outside the program is in the cart. For products, every product must carry a valid promotion with the same code. For categories, the shop compares the number of cart lines with the number of promoted categories it found, so a cart with several lines in one promoted category does not qualify. Two different exclusive programs in one cart cancel each other, and the default is used.
- Higher Priority wins. Among categories, Inclusive beats Exclusive first. Two product promotions with the same priority and level but different codes cancel each other.
- The whole-cart promotion applies when the current time is between its start and end date, in UTC, and the cart total, in euro or in RON with RON-Only Mode, reaches Cart-size Financing Product Value. An empty end date never applies. The dates are read at default level only; the product name and cart size are per store view.

**The badge** resolves the promotion for the one product it is drawn for: on a product page the selected variant, then the parent; in listings and widgets the promotion of a configurable product's lowest-priced enabled, in-stock variant (when several share that price, the first with a promotion), then the parent. The badge then uses that program's plans. A whole-cart promotion with a cart size cannot show on a badge, because a badge has no cart.

**Clearing expired data.** Leanpay Promos > Automatic Removal Of Promotion Data (default level): Enabled switches a daily job on, Start Time sets when it runs, and Frequency only shows the daily choice. The job clears the Leanpay promotion fields of every product whose end date is before today, in every store view. Categories are not cleared. The [runbook](../operations/runbook.md) has the command that runs it at once.

## What the client can change

In the admin, no code: the fields above on each product and category, Leanpay Promos > Multiple Financing Products (Financing Product Name, the two dates, Cart-size Financing Product Value) and the removal group.

## Before you change it

- Checkout, the product page badge and listing badges share one resolution. A change to a rule must keep them in step, or the badge advertises terms the buyer does not get.
- Listings load category promotion data and variants once for the whole page. A new path that resolves promotions per product reintroduces a query per product card.
- The financing product code is Leanpay's. The shop only lists what the import brought, so an admin edit cannot create a program.
