# CityMarket Flutter App — Scaffold

This directory contains the scaffold code for the CityMarket Flutter mobile app.
It provides Dart model classes and an API service that connects to the Laravel REST API.

## What's Included

- `lib/models/` — Complete Dart data models (Product, User, Order, Cart, etc.)
- `lib/services/api_service.dart` — Full REST API client with all endpoints
- `API_DOCS.md` — Comprehensive REST API documentation

## Getting Started

1. Install Flutter SDK (>= 3.0)
2. Create a new Flutter project and copy these files into it
3. Add the `http` package to `pubspec.yaml`:
   ```yaml
   dependencies:
     http: ^1.2.0
   ```
4. Update the base URL in `lib/services/api_service.dart`
5. Use the API service:
   ```dart
   final api = ApiService();
   await api.login('email', 'password');
   final products = await api.getProducts(search: 'shoes');
   ```

## API Endpoints Summary

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | /auth/register | No | Register new user |
| POST | /auth/login | No | Login |
| GET | /categories | No | Category tree |
| GET | /products | No | Product listing with filters |
| GET | /products/{slug} | No | Product detail |
| GET/POST | /cart | Yes | Cart operations |
| GET/POST | /orders | Yes | Order operations |
| GET/POST | /wishlist | Yes | Wishlist |
| GET/POST | /products/{slug}/reviews | Mixed | Product reviews |
| GET/POST/PUT/DELETE | /addresses | Yes | Address management |
| GET | /wallet | Yes | Wallet & transactions |
| POST | /ai/search | No | AI-powered search |
| POST | /chat/send | Yes | AI chat assistant |
| GET | /recommendations | Yes | AI recommendations |
| POST | /admin/ai/query | Admin | AI admin assistant |

## Features Supported

- ✅ Product browsing (search, filter, sort)
- ✅ Shopping cart
- ✅ Checkout (COD/UPI/Card/Wallet)
- ✅ Order management & tracking
- ✅ Wishlist
- ✅ Reviews & ratings
- ✅ Address management
- ✅ Wallet & referrals
- ✅ AI search & chat assistant
- ✅ AI recommendations
