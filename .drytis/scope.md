# Phase 1 Scope

## In Scope
1. **Auth** — register, login, logout; Sanctum tokens for API, session for web; role-based (customer/admin).
2. **Catalog** — categories (hierarchical), brands, products (with multiple images), product images.
3. **Admin Dashboard** (`/admin`) — overview stats, product CRUD, category CRUD, brand CRUD, order list/detail, user list.
4. **Customer Storefront** (`/`) — home (banners, categories, flash deals, trending, recommended), category browse, product listing with filters (category, price, brand, rating, sort), product details, cart, basic checkout (COD/UPI stubs).
5. **REST API** (`/api`) — auth, categories, products, product detail, cart, orders, AI search. JSON, ready for Flutter.
6. **AI Shopping Assistant** — `POST /api/ai/search` and web UI; natural-language → structured filters → product results.
7. **Seed Data** — categories, brands, ~40 products across categories, admin + customer users.

## Out of Scope (Phase 2+)
- Real payment gateway integration
- Order returns/exchanges/invoices
- Reviews & ratings (schema only in Phase 1)
- Wishlist (schema only in Phase 1)
- Price-drop notifications, push notifications
- Referral & wallet system
- AI chatbot, recommendation engine, AI admin assistant

## Deliverables
- Migrated MySQL schema
- Working admin dashboard at `/admin`
- Working storefront at `/`
- Documented REST API at `/api`
- Live AI shopping assistant
- Seed data populated
