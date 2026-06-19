# CityMarket Architecture

## Directory Structure
```
/workspace
├── app/
│   ├── Http/Controllers/
│   │   ├── Api/          # REST API controllers (JSON, for mobile)
│   │   │   ├── AuthController.php
│   │   │   ├── CategoryController.php
│   │   │   ├── ProductController.php
│   │   │   ├── CartController.php
│   │   │   ├── OrderController.php
│   │   │   └── AiSearchController.php
│   │   ├── Admin/        # Admin web controllers
│   │   └── Storefront/   # Customer web controllers
│   ├── Models/           # Eloquent models
│   ├── Services/         # Business logic (AiSearchService, CartService, etc.)
│   └── Traits/
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
├── resources/views/
│   ├── admin/            # Admin dashboard Blade views
│   ├── storefront/       # Customer storefront Blade views
│   ├── components/       # Reusable Blade components
│   └── layouts/          # admin.blade.php, storefront.blade.php
├── routes/
│   ├── web.php           # Storefront + admin routes
│   ├── api.php           # REST API routes
│   └── console.php
└── tests/
```

## Data Flow
- **Web**: Browser → Livewire/Controller → Blade view → MySQL
- **API**: Mobile client → `/api/*` → Api Controller → JSON → MySQL
- **AI Search**: User query → AiSearchService → LLM (OpenAI-compatible) → structured filters → Product::query() → results

## Routing
- `/` — storefront home
- `/category/{slug}` — category browse
- `/product/{slug}` — product details
- `/cart` — cart
- `/checkout` — checkout
- `/admin` — admin dashboard (auth required, admin role)
- `/admin/products`, `/admin/categories`, `/admin/brands`, `/admin/orders`, `/admin/users`
- `/api/*` — REST API (Sanctum token auth)
- `/login`, `/register` — customer auth
- `/admin/login` — admin auth (same users table, role check)

## Auth Strategy
- **Web**: Laravel session auth (default guard)
- **API**: Sanctum tokens (`POST /api/auth/login` returns token; `Bearer` header)
- **Admin**: same `web` guard + middleware checking `role === 'admin'`
