# Phase A — Storefront API completeness

## New endpoints (routes/api.php)
- GET /api/home — hero banners, categories, featured, flash deals, trending (HomeController logic, JSON)
- GET /api/vendors — approved vendor directory (q, paginate)
- GET /api/vendors/{slug} — vendor profile + their active products (q, paginate)
- GET /api/products/{slug}/related — related products
- POST /api/cart/toggle-wishlist — wishlist toggle (like storefront)
- GET /api/cart/count — lightweight count for badges
- POST /api/coupons/validate — code + cart total → discount preview
- POST /api/cart/apply-coupon / DELETE /api/cart/coupon
- GET /api/checkout/summary — items, totals, coupon, shipping, addresses (for checkout screen)
- PUT /api/account/profile — name, phone, avatar update
- POST /api/account/change-password

## Acceptance (running API)
- [ ] GET /api/home returns banners + sections JSON
- [ ] GET /api/vendors and /api/vendors/{slug} return vendor data (suspended → 404)
- [ ] Coupon validate/apply works and rejects invalid codes
- [ ] GET /api/checkout/summary returns totals math matching web checkout
- [ ] Profile update + change password work with Sanctum token
