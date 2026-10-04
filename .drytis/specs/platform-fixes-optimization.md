# Platform Fixes + Advanced UI + Optimization Spec

## Goal
Fix all remaining bugs, upgrade the UI to advanced design, and optimize performance.

## Bugs to Fix
1. **Chat FK mismatch** — `ChatConversation::messages()` hasMany infers `chat_conversation_id` but the migration uses `conversation_id`. The AI support chat returns a fallback "trouble right now" error because message history retrieval throws SQL errors.
2. **AI search LLM failure** — "AI search LLM call failed: unavailable" — the AI service isn't connecting properly or the prompt is malformed.
3. **Support chat returns generic error** — because chat history retrieval fails, the AI has no context.

## UI Upgrades
1. **Advanced product detail** — gallery thumbnails, sticky add-to-cart bar, quantity selector, tabs (description/specs/reviews), share button.
2. **Enhanced cart** — better layout, coupon field, shipping estimate.
3. **Polished shop page** — sidebar filters, active filter chips, grid/list toggle.
4. **Improved checkout** — multi-step flow (address → delivery → payment → review).
5. **Better account pages** — profile header, order timeline, wallet cards.
6. **Admin dashboard** — modern stat cards with sparklines/charts.

## Performance Optimizations
1. **Eager loading** — eliminate N+1 queries (products → images, category, brand).
2. **Query caching** — cache categories, featured products, flash deals.
3. **Database indexes** — ensure indexes on hot query columns.
4. **Asset optimization** — verify Vite build produces minified CSS/JS.
5. **Route/view caching** — ensure production caching is active.

## Acceptance Criteria
- [ ] Chat support works without SQL errors
- [ ] AI search returns real product matches
- [ ] No errors in laravel.log after full test
- [ ] All pages load in <500ms TTFB
- [ ] Product detail has gallery, specs tab, quantity selector
- [ ] Eager loading on all product list queries
- [ ] Caching on catalog endpoints
