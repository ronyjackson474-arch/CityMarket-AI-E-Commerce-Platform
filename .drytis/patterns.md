# CityMarket Coding Patterns

## Naming
- Controllers: `ProductController` (PascalCase, singular resource)
- Models: singular (`Product`), table plural (`products`)
- Migration: `create_products_table`, columns snake_case
- Routes: kebab-case (`/admin/product-categories`)
- Blade views: snake_case (`product_detail.blade.php`)

## Validation
- Form Request classes for all input (`StoreProductRequest`, etc.)
- Always validate API input; return 422 with field errors in JSON.

## Error Handling
- API: return JSON `{message, errors}` with proper HTTP codes (200, 201, 400, 401, 403, 404, 422, 500).
- Web: redirect back with errors flash.

## Security
- Mass-assignment protection: `$fillable` on every model.
- Authorization: policies + middleware for admin routes.
- No raw SQL — Eloquent only.
- Sanitize output in Blade (Blade does this by default via `{{ }}`).

## API Response Format
```json
{
  "data": [...],
  "meta": {"current_page": 1, "total": 100, "per_page": 20}
}
```
Single resource:
```json
{"data": {...}}
```
Error:
```json
{"message": "...", "errors": {"field": ["..."]}}
```

## Testing Conventions
- Feature tests in `tests/Feature/` — hit endpoints, assert DB + JSON.
- Use `RefreshDatabase` trait.
- Factory-generated models with realistic data.
- Test happy path + validation errors + auth.
- Filename: `{Resource}Test.php` (e.g., `ProductApiTest.php`).

## Livewire Components (Phase 1 uses server-rendered Blade with Alpine for interactivity)
- Naming: `Admin\ProductsTable`, `Storefront\CartWidget`
- One component per concern.

## Tailwind
- Use utility classes directly in Blade.
- Common patterns extracted to Blade components (`<x-product-card>`).
- No custom CSS files except a small `app.css` for base styles.
