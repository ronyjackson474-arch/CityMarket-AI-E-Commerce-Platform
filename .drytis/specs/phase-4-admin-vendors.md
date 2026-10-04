# Phase 4 — Admin vendor management

## Goal
Admin approves/suspends vendors and approves vendor products.

## Files
- routes/web.php admin group: VendorsController (index, approve, suspend, update commission)
- resources/views/admin/vendors/index.blade.php (+ detail if needed)
- Admin ProductController index: badge "Vendor: X" + approve action for vendor-submitted inactive products
- Admin dashboard: pending vendors count card

## Acceptance (running app)
- [ ] /admin/vendors lists vendors with status, product count; Approve/Suspend buttons work immediately
- [ ] Approved vendor's shop page goes live; suspended vendor's shop 404s and dashboard locks
- [ ] Admin can approve vendor-submitted products, which then appear in shop
- [ ] Admin dashboard shows pending vendor approvals

## Edge cases
- Suspending a vendor hides their products from storefront (query scoping) without deleting
