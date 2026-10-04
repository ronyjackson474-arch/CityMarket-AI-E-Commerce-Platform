# Phase 1 — Multivendor schema + vendor registration

## Goal
Vendors table, vendor_id on products, /sell registration flow, vendor role.

## Files
- new migration: create_vendors_table + add vendor_id to products
- app/Models/Vendor.php; User::vendor() hasOne, Product::vendor() belongsTo
- routes/web.php: /sell page + POST handler (Storefront\VendorController@register)
- resources/views/storefront/sell.blade.php (landing + registration form)
- AuthController register: accept role selection / shop fields → create user (role=vendor) + pending vendor
- middleware: VendorAuth (auth + role=vendor + approved vendor for dashboard routes)

## Acceptance (running app)
- [ ] /sell page shows vendor benefits + registration form; submitting creates vendor with status pending and shows success message
- [ ] New products table column vendor_id exists; platform products unaffected
- [ ] Vendor can log in and sees "pending approval" state, no dashboard access until approved

## Edge cases
- Duplicate shop slug → validation error
- Customer account cannot access /vendor dashboard
