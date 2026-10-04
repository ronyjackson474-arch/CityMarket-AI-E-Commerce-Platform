# Complete REST API — Blueprint

## Existing (NO rebuild — already in routes/api.php)
Auth (register/login/logout/me, Sanctum), categories, products (index/featured/flash/show), reviews GET/POST, ai/search, cart CRUD, orders CRUD+cancel+return, wishlist add/remove, addresses CRUD, wallet show/transactions, referrals apply, chat, recommendations.

## Conventions
- Sanctum token auth for protected routes; `auth.vendor` + `auth.admin` style closures/middleware for role gate.
- Responses: existing style `{ data, meta }` — keep consistent (no new Resources layer mid-way).
- All vendor/admin write APIs role-checked (403 on wrong role).

## Gaps to build
Phase A — Storefront completeness: home/banners endpoint, vendor directory + vendor shop products, coupon validate/apply (cart + checkout preview), checkout summary endpoint, account profile update, wishlist toggle, cart count, flash/categories meta.
Phase B — Auth completeness: password forgot/reset (token mail-lite), profile update, change password, token refresh (delete+reissue), account deactivate.
Phase C — Vendor APIs: vendor register (mobile), dashboard stats, product CRUD (vendor-scoped, pending approval), vendor orders, profile get/update.
Phase D — Admin APIs: dashboard stats, vendors list/approve/suspend/commission, products list/toggle/CRUD, orders list/status/returns handling, users list/toggle.
Phase E — Docs + sanity: simple API route list doc (userDocs), curl smoke tests of every new endpoint.
