# Spec: Storefront fix + speed + design polish

## Files
- storefront product grid/card component (src/components/*)
- global CSS / tailwind config (image aspect, card sizing)
- next.config / images config for optimization

## Acceptance criteria
- [ ] All product cards render at the same consistent size (image uses fixed aspect-ratio + object-cover)
- [ ] Images use next/image with width/height or fill + sizes → no layout shift
- [ ] Product grid loads fast: lazy loading for below-fold images, priority only on first 4
- [ ] Design polish: better card hover, shadow, rounded corners, price/badge styling, attractive product grid spacing
- [ ] No console errors on storefront
