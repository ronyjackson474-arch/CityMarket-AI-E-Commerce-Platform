# Task: Phase 2 — Commerce Depth

## Objective
Add full commerce features to the platform: reviews, wishlist, address management, order tracking/returns/invoices, user profile, wallet & referral system.

## Files to Create/Modify
- New migrations: wallets, wallet_transactions, referrals, return_requests
- New/updated models: Wallet, WalletTransaction, Referral, ReturnRequest + update Product (review aggregate), User
- New API controllers: ReviewController, WishlistController, AddressController, ReturnController
- New web controllers: ProfileController (orders, profile, wishlist, wallet, addresses)
- New services: WalletService, ReferralService
- New views: storefront/profile/* (dashboard, orders, addresses, wishlist, wallet), storefront/order-detail
- Tests for all new features

## Acceptance Criteria

### Reviews
- [ ] Authenticated user can POST review on a product (rating 1-5, title, body)
- [ ] One review per user per product
- [ ] Product rating + review_count auto-recalculated on review submit
- [ ] Reviews visible on product detail page
- [ ] API: GET /api/products/{slug}/reviews, POST /api/products/{slug}/reviews

### Wishlist
- [ ] Authenticated user can add/remove products to wishlist
- [ ] Wishlist page showing saved products
- [ ] API: GET/POST/DELETE /api/wishlist endpoints

### Addresses
- [ ] User can CRUD addresses (name, phone, address lines, city, state, pincode)
- [ ] Default address selection
- [ ] API: GET/POST/PUT/DELETE /api/addresses

### Order Tracking & Returns
- [ ] Order detail page with status timeline
- [ ] User can request return for delivered orders
- [ ] Admin can manage return requests
- [ ] Invoice download (PDF or HTML printable)
- [ ] API: POST /api/orders/{orderNumber}/return

### Wallet & Referrals
- [ ] Wallet with balance, transactions history
- [ ] Earn wallet points on orders (1% cashback)
- [ ] Referral code generation, invite via code
- [ ] Both referrer and referee get 100 points on signup
- [ ] API: GET /api/wallet, POST /api/referrals/apply
