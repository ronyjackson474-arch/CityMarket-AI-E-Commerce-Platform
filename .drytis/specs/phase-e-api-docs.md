# Phase E — API docs + smoke tests

## Files
- userDocs/api-reference.md — every endpoint: method, path, auth, params, sample response
- (no code) curl smoke script run to verify all new endpoints

## Acceptance
- [ ] api-reference.md lists ALL endpoints (existing + new) with auth requirements
- [ ] Every new endpoint smoke-tested via curl with real tokens (200/4xx as expected)
- [ ] No existing endpoint broken (regression: old routes still respond)
