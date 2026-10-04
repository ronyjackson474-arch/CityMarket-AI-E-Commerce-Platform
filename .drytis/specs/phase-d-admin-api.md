# Phase D — Admin APIs

## New endpoints (prefix /api/admin, sanctum + role admin)
- GET /api/admin/dashboard — stats incl. pending vendors/orders
- GET /api/admin/vendors?status= — list; POST /{id}/approve, /{id}/suspend, /{id}/commission
- GET /api/admin/products (search, vendor badge) ; PATCH /{id}/toggle ; PUT /{id} ; DELETE /{id}
- GET /api/admin/orders, GET /{id}, POST /{id}/status
- GET /api/admin/returns ; POST /{id}/action
- GET /api/admin/users ; POST /{id}/toggle-status

## Acceptance
- [ ] Non-admin gets 403 on every /api/admin/* route
- [ ] Vendor approve/suspend via API reflects on storefront immediately
- [ ] Order status update + return action work via API
- [ ] Users toggle-status works
