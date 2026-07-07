# Visual Enhancement — 3D / Animations / AR

## Goal
Transform CityMarket storefront into an immersive, premium shopping experience with 3D effects, rich animations, an AR product viewer, and modern interaction patterns.

## Approach
Pure CSS + vanilla JS (no heavy 3D libs). Uses:
- **CSS 3D transforms** — tilt cards, perspective depth, flip effects
- **CSS keyframe animations** — floating, glowing, morphing, reveal
- **Canvas particles** — animated background on hero
- **Scroll-driven reveals** — IntersectionObserver-based entrance animations
- **AR Viewer** — CSS 3D product stage with rotation (AR-like product preview, no WebXR dependency)
- **Confetti** — on add-to-cart success
- **Magnetic buttons** — cursor-follow micro-interactions
- **Scroll progress bar** — top-of-page reading indicator

## Files to create / modify

### New files
1. `resources/css/animations.css` — all keyframes, 3D utility classes, particle styles
2. `resources/js/effects.js` — scroll reveal, 3D tilt, particles, confetti, magnetic buttons, scroll progress, AR viewer init

### Modified files
3. `resources/css/app.css` — import animations.css, add new theme utilities
4. `resources/js/app.js` — import effects.js
5. `resources/views/components/layouts/storefront.blade.php` — scroll progress bar, cursor glow, enhanced header (glass blur + animated nav), particle canvas container
6. `resources/views/components/product-card.blade.php` — add `card-tilt` class for 3D tilt hover, glow border
7. `resources/views/storefront/home.blade.php` — immersive 3D hero with floating shapes + particle bg, animated section headers, parallax banners
8. `resources/views/storefront/product-detail.blade.php` — 3D rotating product viewer stage + AR "View in AR" button with modal

## Acceptance Criteria
- [ ] Scroll progress bar visible at top, fills as user scrolls
- [ ] Product cards tilt in 3D on mouse hover (perspective + rotateX/Y)
- [ ] Hero section has animated floating geometric shapes + canvas particle background
- [ ] All sections reveal with smooth entrance animation on scroll (IntersectionObserver)
- [ ] Product detail page has a 3D rotating product stage (drag-to-rotate or auto-rotate)
- [ ] "View in AR" button on product detail opens an immersive AR-style modal viewer
- [ ] Add-to-cart triggers confetti burst
- [ ] Header has glassmorphism blur effect when scrolled
- [ ] Buttons have magnetic hover effect (follow cursor slightly)
- [ ] All animations are GPU-accelerated (transform/opacity only), performant on mobile
- [ ] `npm run build` succeeds without errors
- [ ] Preview loads without console errors
