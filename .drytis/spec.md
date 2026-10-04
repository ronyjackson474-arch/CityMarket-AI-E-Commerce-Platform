# CityMarket — AI E-Commerce Platform

## Overview
CityMall-inspired e-commerce marketplace. API-first Laravel backend serves a customer storefront, admin dashboard, and REST API (ready for Flutter mobile). Includes an AI shopping assistant.

## Tech Stack
- **Backend**: Laravel 11 (PHP 8.4), Sanctum SPA auth
- **DB**: MySQL (auto-provisioned)
- **Frontend**: Blade + Livewire 3 + Alpine.js + Tailwind CSS
- **AI**: OpenAI-compatible API via in-platform key

## Key Decisions
- **Single codebase** serves web (Blade/Livewire) + mobile (REST API under `/api`).
- **Role-based auth**: `customer` and `admin` roles on the `users` table.
- **API auth**: Laravel Sanctum (token-based for `/api`, session for web).
- **Admin** at `/admin`, customer storefront at `/`.
- **AI search** translates natural-language queries into structured product filters via an LLM, then returns matching products.

## Phases
- **Phase 1** (this session): foundation, catalog, cart, basic checkout, admin CRUD, AI search, seed data.
- **Phase 2**: full checkout/payments, order tracking, returns, reviews, wishlist, inventory alerts, referrals.
- **Phase 3**: AI chatbot, recommendation engine, AI admin assistant, advanced campaigns.
- **Phase 4**: Flutter mobile app (separate environment).

## Project URLs
- Preview: https://citymarket-ai-e-comm-2sb7p3.drytis.dev/
- Admin login: admin@citymarket.test / password (seeded)
- Customer login: customer@citymarket.test / password (seeded)
