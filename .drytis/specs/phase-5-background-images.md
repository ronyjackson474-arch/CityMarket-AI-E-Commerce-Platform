# Phase 5 — Attractive background + product image presentation

## Goal
Richer page backgrounds and premium image presentation.

## Files
- resources/css/app.css (+animations.css): ambient mesh-gradient/aurora page background (body/section-level, GPU-friendly, respects prefers-reduced-motion), subtle pattern accents for hero & category sections
- resources/views/components/product-card.blade.php: branded placeholder tile for missing images (gradient + icon), refined frame
- product-detail gallery: lightbox with click-to-open, arrow/keyboard navigation, zoom-on-hover lens, gradient-framed thumbnails

## Acceptance (running app)
- [ ] Storefront background shows soft animated gradient/ambient depth, no layout shift, no perf regression
- [ ] Products without images show branded placeholder tile instead of blank
- [ ] Product detail: clicking main image opens lightbox; arrow keys navigate; thumbnails styled
- [ ] Reduced-motion users see static background

## Edge cases
- Lightbox works on mobile (swipe optional, close button always visible)
