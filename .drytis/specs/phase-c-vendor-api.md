# Phase C — Vendor APIs

## New endpoints (prefix /api/vendor, sanctum + role vendor + approved)
- POST /api/vendor/register — become vendor (auth user, no existing vendor)
- GET /api/vendor/status — current vendor + status (pending users poll this)
- GET /api/vendor/dashboard — stats (products, active, pending, units_sold, revenue)
- GET /api/vendor/products — own products, paginate
- POST /api/vendor/products — create (is_active=false)
- GET/PUT/DELETE /api/vendor/products/{id} — own-scoped; 404 cross-vendor
- GET /api/vendor/orders — own line items
- GET/PUT /api/vendor/profile

## Acceptance
- [ ] Vendor can register via API and gets pending status
- [ ] Pending vendor gets 403 on dashboard routes
- [ ] Product CRUD works; cross-vendor product access → 404
- [ ] Orders endpoint returns only own items
