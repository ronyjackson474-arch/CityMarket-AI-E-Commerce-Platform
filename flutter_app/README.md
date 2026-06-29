# CityMarket — Flutter App

AI-powered e-commerce shopping app for Android & iOS, built with Flutter and
the CityMarket Laravel REST API.

## Features

- **Auth** — Login, Register, logout, persistent sessions (SharedPreferences).
- **Home** — Greeting, search bar, category strip, flash deals with live
  countdown timers, featured grid, AI recommendations.
- **Catalog** — Category tree browse, product listing with filters (price,
  rating, on-sale) + sort (newest, price, rating) + infinite scroll.
- **Product Detail** — Image carousel, discount badge, specs, description,
  reviews preview, add-to-cart, wishlist toggle, share.
- **Reviews** — Full review list + write-a-review with star picker.
- **Cart** — Line items, quantity stepper, swipe-to-remove, totals, checkout.
- **Checkout** — Address select/add inline, payment method, coupon code,
  order summary, place order.
- **Orders** — Order list, order detail with status timeline, cancel, return.
- **Wishlist** — Grid of saved products with remove.
- **Account** — Profile card, edit profile, orders, wallet, addresses, about,
  logout. Guest mode with sign-in CTA.
- **Addresses** — List, add, edit, delete, set default.
- **Wallet** — Balance card, referral code apply, transaction history.
- **AI Search** — Natural-language product search with AI interpretation
  display (keywords, category, price range).
- **AI Chat Assistant** — Conversational shopping assistant with quick prompts.

## Tech Stack

- **Flutter** ≥ 3.10 / Dart ≥ 3.0
- **State**: Provider + ChangeNotifier
- **HTTP**: `http` package
- **Images**: `cached_network_image`
- **Fonts**: Google Fonts (Poppins)
- **Formatting**: `intl` (Indian Rupee currency)
- **Animations**: `flutter_staggered_animations`, `smooth_page_indicator`

## Getting Started

```bash
cd flutter_app
flutter pub get
flutter run
```

> The app connects to the CityMarket REST API at
> `https://citymarket-ai-e-comm-2sb7p3.drytis.dev/api` (configured in
> `lib/constants/app_constants.dart`).
>
> **Demo account:** `customer@citymarket.test` / `password` (tap "Use demo
> account" on the login screen).

## Project Structure

```
lib/
├── main.dart                    # App entry, providers, theme
├── constants/app_constants.dart # API base URL, config
├── theme/app_theme.dart         # Material 3 theme, colors, gradients
├── models/                      # Data models (JSON → Dart)
│   ├── ai.dart                  # AI search + chat models
│   ├── order.dart              # Cart, Order, Address, Review
│   ├── paginated.dart          # Generic Laravel pagination parser
│   ├── product.dart            # Product, Category, Brand, ProductImage
│   └── user.dart               # User, Wallet, AuthResponse
├── services/
│   ├── api_service.dart         # HTTP client, all endpoints
│   └── storage_service.dart     # SharedPreferences token mgmt
├── providers/app_provider.dart  # Global state (auth, cart/wishlist counts)
├── utils/
│   ├── auth_guard.dart          # Login-gate for protected actions
│   ├── format.dart             # Currency / date helpers
│   └── nav.dart                # Named navigation helpers
├── widgets/                     # Shared UI components
│   ├── app_network_image.dart
│   ├── buttons.dart            # GradientButton, SectionHeader, QuantityStepper
│   ├── product_card.dart       # ProductCard, ProductGrid
│   ├── rating_stars.dart
│   ├── shimmer.dart
│   └── states.dart             # EmptyState, ErrorState, LoadingOverlay
└── screens/                     # All app screens (22 total)
    ├── main_shell.dart          # Bottom navigation host
    ├── auth/                   # Splash, Login, Register
    ├── home/                   # Home + home widgets
    ├── catalog/                # Categories, Category Products
    ├── product/                # Product Detail, Reviews
    ├── cart/                   # Cart, Checkout, Order Placed
    ├── order/                  # Orders, Order Detail
    ├── wishlist/               # Wishlist
    ├── account/                # Account, Addresses, Wallet, Edit Profile, About
    └── ai/                     # AI Search, AI Chat
```

## API Integration

All API response parsing matches the **verified** shapes from the live Laravel
backend:

- Auth responses unwrap `data.token` (not top-level).
- Products unwrap from `data` array in pagination envelope.
- Cart parses `data` (items with nested `product`) + separate top-level
  `summary` (item_count, subtotal, shipping, total).
- Wishlist returns paginated products with `pivot`.
- Prices/ratings are strings (`"129999.00"`, `"4.7"`) — parsed to `double`.
- Specifications arrive as a JSON-encoded string — decoded to `Map`.
