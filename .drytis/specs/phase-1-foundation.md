# Task: Phase 1 — Foundation, Catalog, Storefront, Admin, AI Search

## Objective
Build a working e-commerce platform: Laravel backend + MySQL schema, customer storefront, admin dashboard, REST API for mobile, and AI shopping assistant. Seed data included.

## Files to Create/Modify
- `database/migrations/*.php` — schema for all tables
- `app/Models/*.php` — Eloquent models with relationships
- `app/Http/Controllers/Api/*.php` — REST API controllers
- `app/Http/Controllers/Admin/*.php` — admin web controllers
- `app/Http/Controllers/Storefront/*.php` — storefront web controllers
- `app/Services/AiSearchService.php` — AI search logic
- `app/Http/Middleware/IsAdmin.php` — admin role guard
- `app/Http/Requests/*.php` — form requests for validation
- `routes/web.php`, `routes/api.php` — route definitions
- `resources/views/layouts/*.blade.php` — admin + storefront layouts
- `resources/views/admin/*.blade.php` — admin views
- `resources/views/storefront/*.blade.php` — storefront views
- `resources/views/components/*.blade.php` — reusable components
- `database/seeders/*.php` — seed data
- `database/factories/*.php` — model factories
- `tests/Feature/*Test.php` — feature tests

## Acceptance Criteria

### 1. Database & Migrations
- [ ] All tables from schema.md exist with correct columns, types, FKs
- [ ] `php artisan migrate` runs cleanly on fresh DB
- [ ] Indexes on FK columns and unique constraints (slug, sku, order_number)

### 2. Models & Relationships
- [ ] Every table has a model with correct `$fillable`, `$casts`, relationships
- [ ] `Product` has `category`, `brand`, `images`, `reviews`, `wishlists`
- [ ] `Category` has self-referential `children()` / `parent()`
- [ ] `User` has `orders`, `reviews`, `cart`, `addresses`, `wishlists`

### 3. REST API
- [ ] `POST /api/auth/register` — creates user, returns token
- [ ] `POST /api/auth/login` — returns token
- [ ] `GET /api/categories` — list categories (tree)
- [ ] `GET /api/products` — paginated list with filters (category, brand, price_min, price_max, sort, search)
- [ ] `GET /api/products/{slug}` — product detail with images, category, brand
- [ ] `POST /api/cart/add` — requires auth, adds to cart
- [ ] `GET /api/cart` — requires auth, returns cart items
- [ ] `POST /api/cart/remove` — removes item
- [ ] `POST /api/orders` — requires auth, creates order from cart, clears cart
- [ ] `POST /api/ai/search` — natural-language search returns product matches

### 4. Customer Storefront
- [ ] Home page loads: banners, categories, flash deals, trending products
- [ ] Category browse page with product grid + filters
- [ ] Product detail page with images, price, add-to-cart
- [ ] Cart page: update qty, remove, totals
- [ ] Checkout page: address, payment method, place order
- [ ] Customer login/register pages

### 5. Admin Dashboard
- [ ] `/admin/login` — admin login
- [ ] `/admin` — dashboard with stats (users, orders, revenue, products, low stock)
- [ ] `/admin/products` — list with search, create/edit form (name, category, brand, price, stock, images)
- [ ] `/admin/categories` — tree list, create/edit
- [ ] `/admin/brands` — list, create/edit
- [ ] `/admin/orders` — list, view detail, update status
- [ ] `/admin/users` — list users
- [ ] All admin routes protected by IsAdmin middleware

### 6. AI Shopping Assistant
- [ ] `AiSearchService` calls OpenAI-compatible LLM with the user query
- [ ] LLM returns structured filters (category, price range, keywords, sort)
- [ ] Service maps filters to a Product query, returns matches
- [ ] API endpoint `POST /api/ai/search` returns `{query, interpretation, products}`
- [ ] Web UI on storefront with chat-like input

### 7. Seed Data
- [ ] Categories (Fashion, Electronics, Home, Beauty, Grocery, Sports)
- [ ] Brands (Nike, Adidas, Apple, Samsung, Sony, Puma, etc.)
- [ ] ~40 products across categories with images (placeholder URLs)
- [ ] Admin user: admin@citymarket.test / password
- [ ] Customer user: customer@citymarket.test / password

### 8. Testing
- [ ] Feature tests for API endpoints (auth, products, cart, orders)
- [ ] Feature test for AI search (mocked LLM response)
- [ ] All tests pass with `php artisan test`

### 9. Infrastructure
- [ ] All env keys saved via backend (no hardcoded creds/URLs in source)
- [ ] Setup script runs install + migrate + seed + build
- [ ] Caddy PHP proxy at `/` serving the app
- [ ] Preview URL returns 200
