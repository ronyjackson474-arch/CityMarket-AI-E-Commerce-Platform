# CityMarket REST API Documentation

Base URL: `https://citymarket-ai-e-comm-2sb7p3.drytis.dev/api`

## Authentication

All authenticated endpoints require a Bearer token in the Authorization header:
```
Authorization: Bearer <token>
```

### POST /auth/register
Register a new customer account.

**Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "+919876543210"
}
```

**Response (201):**
```json
{
  "token": "1|abc123...",
  "user": { "id": 1, "name": "John Doe", "email": "john@example.com", "role": "customer" }
}
```

### POST /auth/login
Login with email/password.

**Body:** `{ "email": "...", "password": "..." }`

**Response (200):** Same as register.

### POST /auth/logout *(auth)*
Revoke the current token.

### GET /auth/me *(auth)*
Get the authenticated user's profile.

---

## Categories

### GET /categories
Get category tree (parent categories with children).

### GET /categories/{id}
Get a single category.

---

## Products

### GET /products
List products with filters.

**Query params:**
- `category` — category ID
- `category_slug` — category slug
- `brand` — brand ID
- `price_min`, `price_max` — price range
- `min_rating` — minimum rating (1-5)
- `search` — search term
- `sort` — `price_asc`, `price_desc`, `rating`, `newest`
- `on_sale` — 1 for products on sale
- `per_page` — items per page (default 20)

### GET /products/featured
Get featured products.

### GET /products/flash-deals
Get flash deal products.

### GET /products/{slug}
Get a single product by slug with images, reviews, category, brand.

---

## Cart *(auth)*

### GET /cart
Get cart with items and totals.

### POST /cart/add
**Body:** `{ "product_id": 1, "quantity": 2 }`

### POST /cart/update
**Body:** `{ "cart_item_id": 1, "quantity": 3 }`

### POST /cart/remove
**Body:** `{ "cart_item_id": 1 }`

### POST /cart/clear
Clear all cart items.

---

## Orders *(auth)*

### GET /orders
List user's orders.

### GET /orders/{orderNumber}
Get order details with items.

### POST /orders
Create order from cart.

**Body:**
```json
{
  "shipping_address": {
    "name": "John Doe",
    "phone": "+919876543210",
    "address_line1": "123 Main St",
    "city": "Mumbai",
    "state": "Maharashtra",
    "pincode": "400001"
  },
  "payment_method": "cod",
  "coupon_code": "WELCOME10"
}
```

### POST /orders/{orderNumber}/cancel
Cancel a pending/confirmed order.

### POST /orders/{orderNumber}/return
Request a return for a delivered order.

**Body:** `{ "reason": "damaged", "description": "..." }`

---

## Wishlist *(auth)*

### GET /wishlist
Get wishlisted products.

### POST /wishlist/add
**Body:** `{ "product_id": 1 }`

### POST /wishlist/remove
**Body:** `{ "product_id": 1 }`

---

## Reviews

### GET /products/{slug}/reviews
Get reviews for a product.

### POST /products/{slug}/reviews *(auth)*
Post a review.

**Body:** `{ "rating": 5, "title": "Great!", "body": "Loved it" }`

---

## Addresses *(auth)*

### GET /addresses
List saved addresses.

### POST /addresses
**Body:** `{ "name": "...", "phone": "...", "address_line1": "...", "city": "...", "state": "...", "pincode": "..." }`

### PUT /addresses/{id}
Update an address.

### DELETE /addresses/{id}
Delete an address.

---

## Wallet *(auth)*

### GET /wallet
Get wallet balance and recent transactions.

### GET /wallet/transactions
Get paginated transaction history.

### POST /referrals/apply
Apply a referral code.

**Body:** `{ "referral_code": "CMABCD1234" }`

---

## AI Features *(auth)*

### POST /ai/search
Natural language product search.

**Body:** `{ "query": "I need running shoes under 2000" }`

**Response:**
```json
{
  "ai_interpreted": true,
  "filters": { "search": "running shoes", "price_max": 2000 },
  "products": [...],
  "ai_response": "I found 5 running shoes under ₹2000..."
}
```

### GET /chat/conversations
List chat conversations.

### POST /chat/send
Send a message to the AI shopping assistant.

**Body:** `{ "message": "Hello, I need help", "conversation_id": null }`

### GET /recommendations
Get AI-powered product recommendations based on browsing/purchase history.

**Query:** `?limit=10`

---

## Admin AI *(auth + admin)*

### POST /admin/ai/query
Ask the AI admin assistant about store performance.

**Body:** `{ "question": "Show today's sales summary" }`

---

## Response Formats

### Product Object
```json
{
  "id": 1,
  "name": "Product Name",
  "slug": "product-name-abc123",
  "description": "...",
  "price": "999.00",
  "sale_price": "799.00",
  "stock": 50,
  "rating": "4.5",
  "review_count": 12,
  "is_featured": true,
  "is_flash_deal": false,
  "category": { "id": 1, "name": "Electronics", "slug": "electronics" },
  "brand": { "id": 1, "name": "BrandName", "slug": "brandname" },
  "images": [{ "id": 1, "image_path": "...", "is_primary": true }]
}
```

### Order Object
```json
{
  "id": 1,
  "order_number": "CM1700000001",
  "status": "confirmed",
  "subtotal": "1500.00",
  "discount": "150.00",
  "shipping": "0.00",
  "total": "1350.00",
  "payment_method": "cod",
  "payment_status": "pending",
  "items": [...],
  "created_at": "2024-01-01T00:00:00.000000Z"
}
```

## Error Responses
```json
{
  "message": "Error description",
  "errors": { "field": ["Validation error message"] }
}
```

## Status Codes
- `200` — Success
- `201` — Created
- `400` — Bad Request
- `401` — Unauthorized
- `403` — Forbidden
- `404` — Not Found
- `422` — Validation Error
- `500` — Server Error
