# Phase 3 — Vendor dashboard (seller panel)

## Goal
Approved vendors manage own products/orders at /vendor/*.

## Files
- routes/web.php: prefix vendor, middleware vendor auth
- app/Http/Controllers/Vendor/DashboardController, ProductController, OrderController, ProfileController
- resources/views/vendor/** (layout reusing admin components styling): dashboard stats (products, orders, revenue), product CRUD (creates products with vendor_id, is_active=0 pending admin approval), orders list (only order_items of own products), profile edit (shop_name, logo, banner, description)

## Acceptance (running app)
- [ ] Vendor dashboard shows own stats only
- [ ] Vendor can add/edit products; they stay inactive until admin approves
- [ ] Vendor cannot see or edit other vendors' products (403/404)
- [ ] Vendor orders view lists only line items belonging to their products
- [ ] Vendor can update shop profile (name, logo URL, banner, description)

## Edge cases
- Suspended vendor → middleware redirects with message
- Stock/price validation same rules as admin forms
