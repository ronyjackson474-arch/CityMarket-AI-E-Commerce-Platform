Symptom: products/categories/users disappear from DB between sessions (twice: 2026-09-17 ~21:15 and ~22:30 UTC). No code path truncates products; cause unknown — likely the production container was recreated or DB volume reset. /project-config/setup.sh runs `php artisan db:seed --force` (ProductSeeder is idempotent via updateOrCreate), so seeding on boot restores catalog: run `php artisan db:seed --force` in dev container when the storefront looks empty. Never assume data loss means code bug — check `php artisan tinker` Product::count() first.
## Admin credentials (from UserSeeder)
- admin@citymarket.test / password (role=admin)
- 6 customer demo accounts also seeded, password=password
