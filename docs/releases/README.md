# Release Notes

This directory contains release notes for the Leanpay Payment module, organized by version and date.

## Structure

Release notes are organized chronologically with the most recent releases at the top.

## Format

Each release note file follows the naming convention: `YYYY-MM-DD-feature-name.md`

---

## Recent Releases

### [2026-10-01 - 0.17.0: Category page performance, installment widget fixes](./0.17.0.md)
Removed three query patterns that made the installment box expensive on product listings: the category promotion is now preloaded once per listing instead of once per product card, the installment currency lookup is cached per request instead of scanning the table for every box, and a missing index was added on the columns the installment lookups filter by. Module database time on a category page is down by roughly 98% and no longer scales with the number of products. An empty Minimum or Maximum Order Total no longer hides the installment widget, the allowed views, more information URL, check your limit URL and Leanpay currency settings are now read per store view, and a new admin setting chooses the Hyvä widget design (Default or Narrow).

### [2026-09-15 - 0.16.0: RON-Only Mode, promotion fixes](./0.16.0.md)
Added RON-only mode configuration for the Romanian plugin so it operates entirely in RON without requiring EUR as an allowed currency or a RON to EUR exchange rate. Fixed checkout promotions being ignored for configurable product variants, and time-based promotions now include the full end day and use the store timezone.

### [2026-02-20 - Product Rounding Feature](./2026-02-20-product-rounding.md)
Added product price rounding configuration for Romanian plugin to ensure installment calculator displays for all eligible products.
