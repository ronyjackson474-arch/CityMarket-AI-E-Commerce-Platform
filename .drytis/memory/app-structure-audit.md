# CityMarket Structure Audit (2026)

- Roles: users.role string `customer|admin`; middleware aliases `admin` (IsAdmin) and `vendor` (EnsureVendor). Vendor = row in `vendors` table (1:1 user, status pending/approved/suspended, commission_pct).
- Panels: admin (products/categories/brands/coupons/orders/returns/users/vendors/ai-assistant), vendor panel exists (dashboard, products CRUD, orders, profile), storefront account (orders, wallet, addresses, referrals, wishlist).
- API: rich sanctum surface — auth, cart, checkout, orders, wishlist, addresses, wallet/referrals, chat, recommendations, vendor/* APIs, admin/* APIs.
- Models: 21 incl. Vendor, Wallet, WalletTransaction, ReturnRequest, Campaign.
- Key gap: NO vendor payout/withdrawal/earnings feature anywhere (no model/migration/route/view). Also no admin resource "show" views; vendor order-status update missing in web panel.
