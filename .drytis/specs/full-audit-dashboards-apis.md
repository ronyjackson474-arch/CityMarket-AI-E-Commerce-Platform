# Full Audit, Dashboards & API Completion

## Audit Findings (current state)
- ~170 routes, 12 stale log errors (old MissingAppKey, resolved)
- Vendor web panel EXISTS (dashboard, products, orders, profile) using x-layouts.vendor
- Admin panel EXISTS (dashboard with CSS bar chart, orders, products, users, vendors, coupons, categories, brands, returns, AI assistant)
- User account pages EXIST (index, orders, order-detail, addresses, wallet, referrals)

## Confirmed Gaps
1. **No vendor earnings/withdrawals/payouts** (no tables, no API, no UI)
2. **Vendor order status updates MISSING** in API (vendor read-only; only admin can change status)
3. **Admin coupons/categories CRUD API missing** (web-only parity)
4. **Notifications system missing** (no table, no API)
5. **No banners/hero management** (hero hardcoded in blade)
6. **Dashboards functional but not polished** — CSS bar chart, no Chart.js, basic stat cards

## Phases

### Phase 1 — Bug & Error Audit Sweep
- Test every web screen (home, shop, product, cart, checkout, account/*, admin/*, vendor/*, auth pages) for 200s and Blade errors
- Re-run full API test suite (27+ endpoints)
- Fix all found errors; clear stale logs; verify zero console errors on key pages

### Phase 2 — Vendor Earnings, Withdrawals & Payouts
- Migration: `vendor_withdrawals` table (vendor_id, amount, method, status[pending/approved/rejected/paid], notes, timestamps)
- Vendor earnings calculation: orders (paid/delivered) × (1 − commission_rate), credited on delivery
- API: GET vendor/earnings, GET/POST vendor/withdrawals, GET vendor/payouts summary
- Web: Vendor dashboard earnings card + withdrawals page

### Phase 3 — Vendor Order Fulfilment API
- API: PATCH vendor/orders/{id}/status (vendor can mark processing/shipped/delivered for own order items only)
- Ownership guard: vendor can only update orders containing their products
- Web: action buttons on vendor orders page

### Phase 4 — Admin CRUD API Parity + Notifications
- Admin API: coupons CRUD, categories CRUD, banners CRUD (new banners table + simple hero management)
- Notifications: notifications table + API (list, mark-read, mark-all-read) + bell icon in storefront header

### Phase 5 — Professional Dashboards
- Chart.js via npm for admin (sales line chart, orders by status donut) and vendor (earnings trend)
- Polished admin dashboard: gradient stat cards, sparklines, recent activity feed
- Polished vendor dashboard: earnings overview, payout status, top products, order pipeline
- Polished user account dashboard: profile completeness, order timeline, wallet snapshot, wishlist count

### Phase 6 — Final Verification
- Full regression: API suite + key web journeys (browse→cart→checkout→order→admin status→vendor fulfil→user sees update)
- Zero errors in log; all new endpoints tested

## Acceptance (per phase in tickets)
See each ticket's acceptance criteria.
