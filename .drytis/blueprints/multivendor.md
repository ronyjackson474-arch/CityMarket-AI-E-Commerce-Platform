# Multivendor Conversion — Blueprint

## Architecture
- New `vendors` table: user_id (unique FK), shop_name, slug (unique), logo_url, banner_url, description, status (pending|approved|suspended), rating, commission_pct, timestamps.
- `users.role` gains `vendor` value (existing string column — no schema change).
- `products` table: add nullable `vendor_id` FK (null = platform-owned product). Vendor-created products start `is_active=false` until admin approval.
- Auth: vendor registers via /sell (role=vendor, creates pending vendor row). Vendor dashboard at /vendor/* (middleware auth + role vendor + approved vendor). Admin gets Vendors section (approve/suspend, commission).

## Routes
- Storefront: /sell (vendor registration), /vendor/{slug} (public shop page), /vendors (directory).
- Vendor: /vendor/dashboard, /vendor/products (CRUD own products), /vendor/orders (own items), /vendor/profile.
- Admin: /admin/vendors (index approve/suspend).

## Design
- Attractive background: mesh-gradient / soft animated ambient background behind storefront sections, subtle noise/aurora accents — CSS only, no perf hit (prefers-reduced-motion respected).
- Product images: styled placeholder (branded SVG gradient tile), hover zoom lens on product detail, lightbox gallery with keyboard nav, rounded gradient frames.
