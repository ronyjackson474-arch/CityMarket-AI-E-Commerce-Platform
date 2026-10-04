# CityMarket API Reference

Base URL: `https://<domain>/api` — JSON in/out. Auth: Laravel Sanctum bearer tokens.

**Auth header** (protected routes): `Authorization: Bearer <token>`

---

## Auth
| Method | Path | Auth | Body / Params |
|---|---|---|---|
| POST | /auth/register | — | name, email, password (min 8), phone? |
| POST | /auth/login | — | email, password, device_name? |
| POST | /auth/forgot-password | — | email → returns `reset_token` (dev behaviour) |
| POST | /auth/reset-password | — | email, token, password |
| POST | /auth/logout | ✅ | — |
| GET | /auth/me | ✅ | — |
| POST | /auth/token/refresh | ✅ | device_name? → new token, old revoked |
| POST | /account/deactivate | ✅ | password → locks account |

## Home & Catalog
| Method | Path | Auth | Notes |
|---|---|---|---|
| GET | /home | — | banners, categories, flash_deals, featured, trending, brands |
| GET | /categories | — | |
| GET | /categories/{slug} | — | |
| GET | /products | — | filters: category, category_slug, brand, price_min/max, min_rating, search, sort |
| GET | /products/featured | — | |
| GET | /products/flash-deals | — | |
| GET | /products/{slug} | — | |
| GET | /products/{slug}/related | — | 8 related products |
| GET | /vendors | — | q?, page — approved vendors |
| GET | /vendors/{slug} | — | vendor profile + products (404 if suspended) |
| POST | /ai/search | — | natural language search |
| GET | /recommendations | ✅ | personalised |

## Cart & Checkout
| Method | Path | Auth | Body |
|---|---|---|---|
| GET | /cart | ✅ | — |
| GET | /cart/count | ✅ | — (badge count) |
| POST | /cart/add | ✅ | product_slug или product_id, quantity? |
| POST | /cart/update | ✅ | product_slug/id, quantity |
| POST | /cart/remove | ✅ | product_slug/id |
| POST | /cart/clear | ✅ | — |
| POST | /cart/toggle-wishlist | ✅ | product_slug/id |
| POST | /coupons/validate | ✅ | code → discount preview |
| GET | /checkout/summary | ✅ | ?coupon_code= → items, totals, addresses, payment_methods |
| POST | /orders | ✅ | customer_name, customer_phone, shipping_address, payment_method (cod/upi/card/wallet), coupon_code? |
| GET | /orders | ✅ | — |
| GET | /orders/{orderNumber} | ✅ | — |
| POST | /orders/{orderNumber}/cancel | ✅ | — |
| POST | /orders/{orderNumber}/return | ✅ | reason, description? |

## Wishlist / Addresses / Wallet / Reviews
| Method | Path | Auth |
|---|---|---|
| GET | /wishlist | ✅ |
| POST | /wishlist/add | ✅ |
| POST | /wishlist/remove | ✅ |
| GET/POST | /addresses | ✅ |
| PUT/DELETE | /addresses/{id} | ✅ |
| GET | /wallet | ✅ |
| GET | /wallet/transactions | ✅ |
| POST | /referrals/apply | ✅ |
| GET | /products/{slug}/reviews | — |
| POST | /products/{slug}/reviews | ✅ |
| GET | /chat/conversations · POST /chat/send | ✅ |

## Account
| Method | Path | Body |
|---|---|---|
| PUT | /account/profile | name?, phone?, avatar? (url) |
| POST | /account/change-password | current_password, new_password |

## Vendor APIs
| Method | Path | Auth | Notes |
|---|---|---|---|
| POST | /vendor/register | ✅ | shop_name, description?, logo_url? → pending |
| GET | /vendor/status | ✅ | poll approval state |
| GET | /vendor/dashboard | ✅ vendor* | stats: products/active/pending/units_sold/revenue |
| GET | /vendor/products | ✅ vendor | own products, paginate |
| POST | /vendor/products | ✅ vendor | name, category_id, price, stock, sale_price?, images[] → pending approval |
| GET/PUT/DELETE | /vendor/products/{id} | ✅ vendor | own only (404 otherwise); PUT → back to review |
| GET | /vendor/orders | ✅ vendor | only own line items |
| GET/PUT | /vendor/profile | ✅ vendor | shop_name, description, logo_url, banner_url |

\* vendor = approved vendor required (403 while pending/suspended).

## Admin APIs (admin role; 403 otherwise)
| Method | Path | Notes |
|---|---|---|
| GET | /admin/dashboard | stats incl. pending_vendors |
| GET | /admin/vendors?status= | list |
| POST | /admin/vendors/{id}/approve | |
| POST | /admin/vendors/{id}/suspend | |
| POST | /admin/vendors/{id}/commission | commission_pct |
| GET | /admin/products?search= | vendor badge included |
| PATCH | /admin/products/{id}/toggle | approve/hide |
| DELETE | /admin/products/{id} | |
| GET | /admin/orders?status= | |
| GET | /admin/orders/{id} | |
| POST | /admin/orders/{id}/status | pending/confirmed/packed/shipped/delivered/cancelled |
| GET | /admin/returns | |
| POST | /admin/returns/{id}/action | action: approve/reject |
| GET | /admin/users?search= | |
| POST | /admin/users/{id}/toggle-status | cannot target admins |

## Errors
- 401 — missing/invalid token
- 403 — role required (admin/vendor) or suspended vendor
- 404 — resource not found (incl. suspended vendor shop)
- 422 — validation error (`{ "message", "errors" }`)
- 400 — business rule (empty cart, out of stock)
