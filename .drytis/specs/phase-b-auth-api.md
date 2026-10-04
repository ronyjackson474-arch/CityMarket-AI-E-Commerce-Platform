# Phase B — Auth API completeness

## New endpoints
- POST /api/auth/forgot-password — email → create reset token row, return dev reset token (no mailer configured)
- POST /api/auth/reset-password — token + new password
- POST /api/auth/token/refresh — valid sanctum token → revoke + issue new
- POST /api/account/deactivate — soft lock account (is_active=false) + logout

## Acceptance
- [ ] Forgot → reset flow changes password; old password fails, new works
- [ ] Token refresh issues working new token, old revoked
- [ ] Deactivated user cannot login
