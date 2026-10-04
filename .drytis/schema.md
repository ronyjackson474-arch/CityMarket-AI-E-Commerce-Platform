# CityMarket Database Schema

## Users
```sql
users (
  id, name, email, email_verified_at, password, role ENUM('customer','admin') DEFAULT 'customer',
  phone, avatar, remember_token, timestamps
)
```

## Catalog Tables

### categories
```sql
categories (
  id, parent_id (nullable FK self), name, slug (unique), description, icon, image, is_active BOOL, position INT, timestamps
)
```

### brands
```sql
brands (id, name, slug (unique), logo, description, is_active BOOL, timestamps)
```

### products
```sql
products (
  id, category_id FK, brand_id FK (nullable), name, slug (unique),
  description (text), price DECIMAL(10,2), sale_price DECIMAL(10,2) nullable,
  sku (unique), stock INT, rating DECIMAL(2,1) DEFAULT 0, review_count INT DEFAULT 0,
  is_featured BOOL, is_active BOOL, timestamps
)
```

### product_images
```sql
product_images (id, product_id FK, image_path, is_primary BOOL, position INT, timestamps)
```

## Commerce Tables

### carts + cart_items
```sql
carts (id, user_id FK, session_id (nullable), timestamps)
cart_items (id, cart_id FK, product_id FK, quantity INT, timestamps)
```

### orders + order_items
```sql
orders (
  id, user_id FK, order_number (unique), status ENUM('pending','confirmed','packed','shipped','delivered','cancelled'),
  subtotal DECIMAL(10,2), discount DECIMAL(10,2), shipping DECIMAL(10,2), total DECIMAL(10,2),
  payment_method ENUM('cod','upi','card','wallet'), payment_status ENUM('pending','paid','failed'),
  shipping_address TEXT, customer_name, customer_phone, notes TEXT, timestamps
)
order_items (id, order_id FK, product_id FK, product_name, price DECIMAL(10,2), quantity INT, timestamps)
```

### addresses
```sql
addresses (id, user_id FK, name, phone, address_line1, address_line2, city, state, pincode, is_default BOOL, timestamps)
```

### coupons
```sql
coupons (
  id, code (unique), type ENUM('percentage','fixed'), value DECIMAL(10,2),
  min_order_amount DECIMAL(10,2), max_discount DECIMAL(10,2) nullable,
  usage_limit INT, used_count INT DEFAULT 0, starts_at, expires_at, is_active BOOL, timestamps
)
```

### reviews
```sql
reviews (id, product_id FK, user_id FK, rating INT (1-5), title, body, is_verified_purchase BOOL, timestamps)
```

### wishlists
```sql
wishlists (id, user_id FK, product_id FK, timestamps, UNIQUE(user_id, product_id))
```

## Indexes
- products: category_id, brand_id, is_featured, is_active, (sale_price for deals)
- products.slug, products.sku — unique
- orders.order_number — unique
- All FKs indexed
