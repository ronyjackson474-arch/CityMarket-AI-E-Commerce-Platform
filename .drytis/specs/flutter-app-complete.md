# Flutter App — Complete Build Spec

## Goal
Build the **complete** CityMarket Flutter mobile app: all screens, advanced design, full functionality, no bugs. Targets the live Laravel REST API at `https://citymarket-ai-e-comm-2sb7p3.drytis.dev/api`.

## Constraints
- No Flutter/Dart toolchain in this workspace → cannot run `flutter analyze`. Code must be correct by construction.
- All API response parsing MUST match the **real** shapes probed from the live API (documented below).

## Real API Response Shapes (verified)
- **Login/Register**: `{message, data:{user:{id,name,email,role,phone,avatar,...}, token:"1|..."}}`
- **me**: `{data:{...user...}}`
- **categories**: `{data:[{id,parent_id,name,slug,icon,image,children:[...]}]}`
- **products (list)**: `{current_page, data:[{id,category_id,brand_id,name,slug,description,specifications(string),sku,price(string),sale_price(string|null),stock(int),rating(string|null),review_count,is_featured,is_flash_deal,flash_deal_ends_at,category:{id,name,slug},brand:{id,name,slug,logo},images:[{id,image_path,is_primary,position}]}], ...pagination}`
- **featured / flash-deals / recommendations**: `{data:[...products...]}`
- **product detail**: `{data:{...product...}}`
- **cart**: `{data:{id,user_id,items:[{id,cart_id,product_id,quantity,product:{...}, (images loaded in cart GET)}]}, summary:{item_count,subtotal,shipping,total}}`
- **add to cart**: `{message, data:{...cart...}, summary:{...}}`
- **wishlist**: paginated `{data:[{...full product... pivot:{user_id,product_id}}], ...pagination}`
- **orders**: paginated `{data:[...], ...pagination}`
- **wallet**: `{data:{balance,referral_code,transactions:[...]}}`
- **ai/search**: `{message, data:{query,interpretation:{keywords,category,price_max,ai_interpreted},products:[...]}}`
- **chat/conversations**: `{data:[{id,status,context,created_at}]}`
- **chat/send**: `{conversation_id, message, assistant_message_id}`
- **addresses**: `{data:[{id,name,phone,address_line1,city,state,pincode,is_default}]}`
- **reviews**: paginated `{data:[{id,rating,title,body,is_verified_purchase,user:{name},created_at}]}`

## Architecture
- **State**: Provider + ChangeNotifier. Single `AppProvider` holds auth + cart + wishlist counts; per-screen data fetched locally with FutureBuilder.
- **Services**: `ApiService` (HTTP, token mgmt, auto-unwrap), `StorageService` (SharedPreferences token).
- **Models**: rewritten to match real shapes (User, Category, Product, Brand, Cart, CartItem, Order, OrderItem, Address, Review, Wallet, WalletTransaction, Conversation, ChatMessage, AiSearchResult, Paginated).
- **Theme**: Material 3, custom color scheme, Google Fonts (Poppins), gradients, rounded cards, shimmer loading.
- **Nav**: bottom nav (Home, Categories, Cart, Wishlist, Account) + Navigator push for detail screens.

## Screens (all must be implemented)
1. Splash
2. Login
3. Register
4. Home (greeting, search bar, categories, flash deals w/ countdown, featured grid, recommendations)
5. Categories (tree browse)
6. Category products (list w/ filters & sort)
7. Product Detail (image carousel, price/discount, specs, reviews, add-to-cart, wishlist, share)
8. Cart (line items, qty stepper, totals, checkout CTA)
9. Checkout (address select/add, payment method, coupon, place order)
10. Order Placed (success)
11. Orders list
12. Order Detail (items, status timeline, cancel, return)
13. Wishlist
14. Account/Profile (user info, wallet, addresses, orders, logout)
15. Addresses (list + add/edit)
16. Wallet (balance, referral, transactions)
17. AI Search (natural language → results)
18. AI Assistant Chat
19. Search results
20. Edit Profile
21. Notifications / Settings (about)
22. Reviews (for a product)

## Shared widgets
- ProductCard, ProductGrid, CategoryChip, RatingStars, PriceText, ShimmerList, EmptyState, ErrorState, LoadingOverlay, GradientButton, QuantityStepper, CartBadge, SearchBar field, SectionHeader, CountdownTimer.

## Acceptance Criteria
- [ ] `pubspec.yaml` with pinned, mutually-compatible package versions
- [ ] `main.dart` with MultiProvider + MaterialApp + theme + splash/auth routing
- [ ] All 22 screens implemented, navigable, with real API data
- [ ] Models parse every real API shape correctly
- [ ] Auth: login/register/logout with token persistence (SharedPreferences); auto-restore on launch
- [ ] Cart count badge in bottom nav reflects server cart
- [ ] Loading shimmer, empty states, error states on every list screen
- [ ] Pull-to-refresh on list screens
- [ ] No hardcoded secrets (API base URL is the public preview URL — fine, it's the public API)
- [ ] Null-safe, no `late` that can throw, no unchecked null derefs
- [ ] Advanced design: gradients, Material 3, smooth, cards, badges, animations
- [ ] Hindi/IN currency formatting (₹) via intl

## Testing
- No `flutter test` available. Correctness verified by static analysis + matching every fromJson to probed API output.
- Tester sub-agent is skipped (Flutter app has no browser-previewable URL in this PHP workspace). Reviewer will be delegated to verify code correctness against this spec.
