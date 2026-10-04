# Phase 2 — Public vendor shop pages + directory

## Goal
/vendors directory and /vendor/{slug} shop page listing that vendor's approved products.

## Files
- Storefront\VendorController: index (directory), show (shop page, paginated products, search)
- resources/views/storefront/vendors.blade.php, vendor-shop.blade.php (banner, logo, rating, product grid using x-product-card)
- Header nav: "Vendors" link; product cards/detail can later show "Sold by {shop}"

## Acceptance (running app)
- [ ] /vendors lists all approved vendors with logo, name, rating, product count
- [ ] /vendor/{slug} shows vendor banner + their active products in the styled grid
- [ ] Suspended/pending vendor pages return 404 for public
- [ ] Product detail page shows "Sold by {vendor shop}" when product has a vendor

## Edge cases
- Vendor with no products → empty state message
- Slug is unique + validated
