<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = $this->catalog();

        foreach ($products as $data) {
            $category = Category::where('name', $data['category'])->first();
            $brand = Brand::where('name', $data['brand'])->first();

            if (! $category) {
                continue;
            }

            $slug = Str::slug($data['name']);

            $product = Product::updateOrCreate(
                ['slug' => $slug],
                [
                    'category_id' => $category->id,
                    'brand_id' => $brand?->id,
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'specifications' => json_encode($data['specifications'] ?? []),
                    'sku' => strtoupper(Str::random(10)),
                    'price' => $data['price'],
                    'sale_price' => $data['sale_price'] ?? null,
                    'stock' => $data['stock'] ?? rand(10, 200),
                    'rating' => $data['rating'] ?? rand(35, 50) / 10,
                    'review_count' => $data['review_count'] ?? rand(5, 500),
                    'is_featured' => $data['featured'] ?? false,
                    'is_flash_deal' => $data['flash_deal'] ?? false,
                    'is_active' => true,
                    'flash_deal_ends_at' => ($data['flash_deal'] ?? false) ? now()->addHours(rand(2, 12)) : null,
                ]
            );

            // Images — use the real local image when it exists, else picsum placeholder
            $product->images()->delete();
            $local = "images/products/{$slug}.jpg";
            if (file_exists(public_path($local))) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => "/" . $local,
                    'is_primary' => true,
                    'position' => 0,
                ]);
            } else {
                foreach (($data['images'] ?? [1]) as $i => $seed) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => "https://picsum.photos/seed/{$slug}-{$seed}/1200/1200",
                        'is_primary' => $i === 0,
                        'position' => $i,
                    ]);
                }
            }
        }
    }

    /**
     * Realistic product catalog spanning categories and brands.
     */
    private function catalog(): array
    {
        $e = 'Electronics';
        $f = 'Fashion';
        $h = 'Home & Kitchen';
        $b = 'Beauty';
        $s = 'Sports';

        return [
            // ---- Electronics / Mobiles ----
            ['name' => 'iPhone 15 Pro Max 256GB', 'category' => $e, 'brand' => 'Apple',
             'price' => 134900, 'sale_price' => 129900, 'stock' => 25, 'rating' => 4.8, 'review_count' => 340,
             'images' => [1, 2, 3],
             'description' => 'The most powerful iPhone ever with the A17 Pro chip, titanium design, and pro camera system.',
             'specifications' => ['Display' => '6.7" Super Retina XDR', 'Chip' => 'A17 Pro', 'Camera' => '48MP Triple', 'Battery' => '29 hours video']],
            ['name' => 'Samsung Galaxy S24 Ultra', 'category' => $e, 'brand' => 'Samsung',
             'price' => 129999, 'sale_price' => 109999, 'stock' => 40, 'rating' => 4.7, 'review_count' => 280,
             'description' => 'Galaxy AI is here. Built-in S Pen, 200MP camera, and titanium frame.',
             'specifications' => ['Display' => '6.8" QHD+', 'Chip' => 'Snapdragon 8 Gen 3', 'Camera' => '200MP Quad']],
            ['name' => 'Redmi Note 13 Pro', 'category' => $e, 'brand' => 'Mi',
             'price' => 25999, 'sale_price' => 21999, 'stock' => 100, 'rating' => 4.3, 'review_count' => 1200,
             'description' => '200MP camera, Snapdragon processor, and 5000mAh battery at an unbeatable price.',
             'specifications' => ['Display' => '6.67" AMOLED', 'Camera' => '200MP', 'Battery' => '5100mAh']],
            ['name' => 'OnePlus 12R 5G', 'category' => $e, 'brand' => 'Samsung',
             'price' => 39999, 'sale_price' => 36999, 'stock' => 60, 'rating' => 4.5,
             'description' => 'Flagship killer with 100W fast charging and Snapdragon 8 Gen 2.'],

            // ---- Electronics / Audio ----
            ['name' => 'boAt Rockerz 450 Bluetooth Headphone', 'category' => $e, 'brand' => 'boAt',
             'price' => 2990, 'sale_price' => 1499, 'stock' => 200, 'rating' => 4.2, 'review_count' => 5400,
             'description' => 'Immersive audio with up to 15 hours playback, lightweight and comfortable.',
             'specifications' => ['Battery' => '15 hours', 'Connectivity' => 'Bluetooth 5.0']],
            ['name' => 'Sony WH-1000XM5 Wireless Headphones', 'category' => $e, 'brand' => 'Sony',
             'price' => 29990, 'sale_price' => 24990, 'stock' => 30, 'rating' => 4.8,
             'description' => 'Industry-leading noise cancellation with crystal-clear hands-free calls.'],
            ['name' => 'Apple AirPods Pro (2nd Gen)', 'category' => $e, 'brand' => 'Apple',
             'price' => 26900, 'sale_price' => 22900, 'stock' => 50, 'rating' => 4.7,
             'description' => 'Adaptive Audio, Active Noise Cancellation, and up to 6 hours of listening time.'],
            ['name' => 'JBL Flip 6 Portable Speaker', 'category' => $e, 'brand' => 'Sony',
             'price' => 9999, 'sale_price' => 7999, 'stock' => 80, 'rating' => 4.6,
             'description' => 'Bold JBL Original Pro Sound in a waterproof portable speaker.'],

            // ---- Electronics / Laptops ----
            ['name' => 'MacBook Air M3 13-inch', 'category' => $e, 'brand' => 'Apple',
             'price' => 114900, 'sale_price' => 109900, 'stock' => 20, 'rating' => 4.9,
             'description' => 'Strikingly thin, blazing fast, and with all-day battery life powered by the M3 chip.'],
            ['name' => 'Dell XPS 13 Plus', 'category' => $e, 'brand' => 'Samsung',
             'price' => 99999, 'stock' => 15, 'rating' => 4.4,
             'description' => 'Stunning edge-to-edge display with Intel 12th Gen processors.'],

            // ---- Fashion / Footwear ----
            ['name' => 'Nike Air Max 270 Running Shoes', 'category' => $f, 'brand' => 'Nike',
             'price' => 8995, 'sale_price' => 5995, 'stock' => 120, 'rating' => 4.6, 'review_count' => 2100,
             'images' => [1, 2, 3, 4],
             'description' => 'Iconic Air Max with the tallest Air unit yet for unbeatable comfort.',
             'specifications' => ['Type' => 'Running', 'Sole' => 'Rubber', 'Closure' => 'Lace-Up']],
            ['name' => 'Adidas Ultraboost 22', 'category' => $f, 'brand' => 'Adidas',
             'price' => 17999, 'sale_price' => 12999, 'stock' => 80, 'rating' => 4.7, 'review_count' => 890,
             'description' => 'Responsive BOOST cushioning with a Primeknit upper for the perfect run.'],
            ['name' => 'Puma RS-X Sneakers', 'category' => $f, 'brand' => 'Puma',
             'price' => 7499, 'sale_price' => 4999, 'stock' => 100, 'rating' => 4.3,
             'description' => 'Retro-inspired design with modern cushioning technology.'],
            ['name' => 'Nike Revolution 6 Next Nature', 'category' => $f, 'brand' => 'Nike',
             'price' => 3295, 'sale_price' => 2199, 'stock' => 150, 'rating' => 4.4, 'review_count' => 3400,
             'description' => 'Lightweight running shoes made with at least 20% recycled content.'],

            // ---- Fashion / Apparel ----
            ['name' => 'Levi\'s 511 Slim Fit Jeans', 'category' => $f, 'brand' => 'Puma',
             'price' => 3999, 'sale_price' => 1999, 'stock' => 200, 'rating' => 4.5,
             'description' => 'Classic slim-fit denim with stretch for all-day comfort.'],
            ['name' => 'Allen Solly Cotton Casual Shirt', 'category' => $f, 'brand' => 'Puma',
             'price' => 2499, 'sale_price' => 1299, 'stock' => 300, 'rating' => 4.2,
             'description' => 'Premium cotton casual shirt for the modern workplace.'],

            // ---- Home & Kitchen ----
            ['name' => 'Philips Air Fryer HD9252', 'category' => $h, 'brand' => 'Philips',
             'price' => 12995, 'sale_price' => 8499, 'stock' => 70, 'rating' => 4.6, 'review_count' => 1200,
             'description' => 'Rapid Air Technology cooks food with little to no oil for healthy meals.',
             'specifications' => ['Capacity' => '4.1L', 'Power' => '1400W']],
            ['name' => 'Prestige Pressure Cooker 5L', 'category' => $h, 'brand' => 'Philips',
             'price' => 1599, 'sale_price' => 1099, 'stock' => 250, 'rating' => 4.4,
             'description' => 'Durable aluminium pressure cooker for fast, healthy cooking.'],
            ['name' => 'Milton Thermosteel Flask 1L', 'category' => $h, 'brand' => 'Philips',
             'price' => 999, 'sale_price' => 649, 'stock' => 400, 'rating' => 4.5,
             'description' => 'Double-wall vacuum insulation keeps liquids hot/cold for 24 hours.'],
            ['name' => 'Sony Bravia 55" 4K Smart TV', 'category' => $h, 'brand' => 'Sony',
             'price' => 89990, 'sale_price' => 69990, 'stock' => 15, 'rating' => 4.7,
             'description' => '4K HDR with Google TV, Dolby Vision, and immersive sound.'],

            // ---- Beauty ----
            ['name' => 'Himalaya Neem Face Wash 200ml', 'category' => $b, 'brand' => 'Himalaya',
             'price' => 295, 'sale_price' => 195, 'stock' => 500, 'rating' => 4.5, 'review_count' => 8900,
             'description' => 'Neem and turmeric face wash for clear, pimple-free skin.'],
            ['name' => 'Lakme 9 to 5 Primer + Matte Lipstick', 'category' => $b, 'brand' => 'Himalaya',
             'price' => 450, 'sale_price' => 349, 'stock' => 300, 'rating' => 4.3,
             'description' => 'Long-lasting matte lipstick with built-in primer for smooth application.'],
            ['name' => 'Nivea Body Lotion 400ml', 'category' => $b, 'brand' => 'Himalaya',
             'price' => 399, 'sale_price' => 299, 'stock' => 400, 'rating' => 4.4,
             'description' => 'Nourishing shea smooth body lotion for 48-hour moisturization.'],

            // ---- Sports ----
            ['name' => 'SG Cricket Bat English Willow', 'category' => $s, 'brand' => 'Puma',
             'price' => 8999, 'sale_price' => 5999, 'stock' => 30, 'rating' => 4.6,
             'description' => 'Professional-grade English willow cricket bat with excellent balance.'],
            ['name' => 'Nike Mercurial Football Boots', 'category' => $s, 'brand' => 'Nike',
             'price' => 6995, 'sale_price' => 4495, 'stock' => 60, 'rating' => 4.5,
             'description' => 'Lightweight football boots with superior grip and ball control.'],
            ['name' => 'Yonex Mavis 350 Shuttlecock (Pack of 6)', 'category' => $s, 'brand' => 'Nike',
             'price' => 1499, 'sale_price' => 1099, 'stock' => 200, 'rating' => 4.4,
             'description' => 'Tournament-grade nylon shuttlecocks for accurate flight.'],
            ['name' => 'Adidas Football Size 5', 'category' => $s, 'brand' => 'Adidas',
             'price' => 1999, 'sale_price' => 1299, 'stock' => 150, 'rating' => 4.6,
             'description' => 'Match-quality football with durable construction and true flight.'],

            // ---- Grocery ----
            ['name' => 'Fortune Sunflower Oil 5L', 'category' => 'Grocery', 'brand' => 'Himalaya',
             'price' => 1199, 'sale_price' => 899, 'stock' => 600, 'rating' => 4.3,
             'description' => 'Refined sunflower oil with Vitamin A & D for healthy cooking.'],
            ['name' => 'Tata Salt 1kg (Pack of 4)', 'category' => 'Grocery', 'brand' => 'Himalaya',
             'price' => 120, 'stock' => 1000, 'rating' => 4.7,
             'description' => 'Iodized salt trusted by millions of Indian households.'],
            ['name' => 'Aashirvaad Atta 5kg', 'category' => 'Grocery', 'brand' => 'Himalaya',
             'price' => 299, 'sale_price' => 249, 'stock' => 800, 'rating' => 4.6,
             'description' => 'Premium whole wheat flour for soft, fluffy rotis.'],
            ['name' => 'Tetley Green Tea Bags (Pack of 25)', 'category' => 'Grocery', 'brand' => 'Himalaya',
             'price' => 199, 'sale_price' => 149, 'stock' => 500, 'rating' => 4.3,
             'description' => 'Refreshing green tea bags packed with antioxidants.'],

            // ---- Additional Fashion ----
            ['name' => 'Nike Dri-FIT Training T-Shirt', 'category' => $f, 'brand' => 'Nike',
             'price' => 1295, 'sale_price' => 795, 'stock' => 200, 'rating' => 4.5,
             'description' => 'Moisture-wicking Dri-FIT fabric keeps you dry during workouts.'],
            ['name' => 'Adidas Track Pants', 'category' => $f, 'brand' => 'Adidas',
             'price' => 2499, 'sale_price' => 1799, 'stock' => 150, 'rating' => 4.4,
             'description' => 'Comfortable track pants with iconic 3-stripes design.'],

            // ---- Additional Electronics ----
            ['name' => 'Mi Smart Band 8', 'category' => $e, 'brand' => 'Mi',
             'price' => 3499, 'sale_price' => 2799, 'stock' => 120, 'rating' => 4.4,
             'description' => 'AMOLED display, 150+ sports modes, and 16-day battery life.'],
            ['name' => 'boAt Airdopes 141 Wireless Earbuds', 'category' => $e, 'brand' => 'boAt',
             'price' => 2990, 'sale_price' => 1299, 'stock' => 300, 'rating' => 4.2, 'review_count' => 8900,
             'description' => 'Up to 42 hours playback, ENx technology, and Beast Mode for gaming.'],
            ['name' => 'Logitech MX Master 3S Mouse', 'category' => $e, 'brand' => 'Sony',
             'price' => 9995, 'sale_price' => 7995, 'stock' => 60, 'rating' => 4.8,
             'description' => 'Premium wireless mouse with ultra-quiet clicks and fast scrolling.'],

            // ---- Additional Home ----
            ['name' => 'Godrej Aer Room Freshener', 'category' => $h, 'brand' => 'Philips',
             'price' => 279, 'stock' => 500, 'rating' => 4.2,
             'description' => 'Long-lasting room freshener with refreshing fragrance.'],

            // ---- Additional Sports ----
            ['name' => 'Nike Pro Combat Compression Shorts', 'category' => $s, 'brand' => 'Nike',
             'price' => 1995, 'sale_price' => 1395, 'stock' => 100, 'rating' => 4.4,
             'description' => 'Compression fit for muscle support during intense workouts.'],

            // ---- Additional Beauty ----
            ['name' => 'Mamaearth Onion Hair Oil 250ml', 'category' => $b, 'brand' => 'Himalaya',
             'price' => 449, 'sale_price' => 329, 'stock' => 400, 'rating' => 4.3,
             'description' => 'Onion oil and redensyl for hair growth and scalp nourishment.'],

            // ==================== EXPANDED CATALOG ====================

            // ---- Fashion (Men) ----
            ['name' => 'Levi\'s 511 Slim Fit Jeans', 'category' => $f, 'brand' => 'Levis',
             'price' => 3999, 'sale_price' => 2799, 'stock' => 60, 'rating' => 4.6,
             'description' => 'Iconic slim fit jeans with stretch denim for all-day comfort.'],
            ['name' => 'US Polo Assn Checked Casual Shirt', 'category' => $f, 'brand' => 'US Polo',
             'price' => 2499, 'sale_price' => 1499, 'stock' => 80, 'rating' => 4.4,
             'description' => 'Classic checked casual shirt in breathable cotton.'],
            ['name' => 'Puma Solid Polo T-Shirt', 'category' => $f, 'brand' => 'Puma',
             'price' => 1499, 'sale_price' => 899, 'stock' => 120, 'rating' => 4.3,
             'description' => 'Soft cotton polo with embroidered logo, regular fit.'],
            ['name' => 'Jack & Jones Bomber Jacket', 'category' => $f, 'brand' => 'Jack & Jones',
             'price' => 5999, 'sale_price' => 3599, 'stock' => 35, 'rating' => 4.5,
             'description' => 'Trendy bomber jacket with ribbed cuffs and hem.'],

            // ---- Fashion (Women) ----
            ['name' => 'Libas Printed Anarkali Kurta', 'category' => $f, 'brand' => 'Libas',
             'price' => 2199, 'sale_price' => 1319, 'stock' => 90, 'rating' => 4.5,
             'description' => 'Elegant printed Anarkali kurta in flowy rayon fabric.'],
            ['name' => 'ONLY High-Rise Skinny Jeans', 'category' => $f, 'brand' => 'ONLY',
             'price' => 3499, 'sale_price' => 2099, 'stock' => 55, 'rating' => 4.4,
             'description' => 'Figure-flattering high-rise skinny jeans with stretch.'],
            ['name' => 'Vero Moda Floral Wrap Dress', 'category' => $f, 'brand' => 'Vero Moda',
             'price' => 2999, 'sale_price' => 1799, 'stock' => 45, 'rating' => 4.6,
             'description' => 'Feminine floral wrap dress perfect for brunch and outings.'],
            ['name' => 'Biba Straight Cotton Suit Set', 'category' => $f, 'brand' => 'Biba',
             'price' => 3299, 'sale_price' => 2499, 'stock' => 40, 'rating' => 4.5,
             'description' => 'Straight kurta with trousers and dupatta in pure cotton.'],

            // ---- Fashion (Footwear) ----
            ['name' => 'Adidas Ultraboost Light', 'category' => $f, 'brand' => 'Adidas',
             'price' => 19999, 'sale_price' => 13999, 'stock' => 30, 'rating' => 4.7,
             'description' => 'Responsive BOOST cushioning for effortless everyday runs.'],
            ['name' => 'Campus Running Shoes', 'category' => $f, 'brand' => 'Campus',
             'price' => 2299, 'sale_price' => 1379, 'stock' => 150, 'rating' => 4.2,
             'description' => 'Lightweight running shoes with memory foam insole.'],
            ['name' => 'Bata Formal Leather Shoes', 'category' => $f, 'brand' => 'Bata',
             'price' => 2799, 'sale_price' => 1959, 'stock' => 70, 'rating' => 4.1,
             'description' => 'Genuine leather formal shoes with cushioned footbed.'],
            ['name' => 'Crocs Classic Clog', 'category' => $f, 'brand' => 'Crocs',
             'price' => 3495, 'sale_price' => 2449, 'stock' => 85, 'rating' => 4.6,
             'description' => 'Iconic comfort clog, lightweight and water-friendly.'],

            // ---- Fashion (Bags) ----
            ['name' => 'American Tourister Laptop Backpack 32L', 'category' => $f, 'brand' => 'American Tourister',
             'price' => 3499, 'sale_price' => 2099, 'stock' => 65, 'rating' => 4.5,
             'description' => 'Water-resistant laptop backpack with 3 compartments.'],
            ['name' => 'Lavie Women\'s Tote Handbag', 'category' => $f, 'brand' => 'Lavie',
             'price' => 2599, 'sale_price' => 1559, 'stock' => 50, 'rating' => 4.4,
             'description' => 'Spacious tote bag in premium vegan leather.'],

            // ---- Electronics (Laptops) ----
            ['name' => 'HP Pavilion x360 Touch Laptop', 'category' => $e, 'brand' => 'HP',
             'price' => 62990, 'sale_price' => 54990, 'stock' => 20, 'rating' => 4.4,
             'description' => 'Convertible 14-inch touch laptop with 12th Gen Intel i5.'],
            ['name' => 'Lenovo IdeaPad Slim 5', 'category' => $e, 'brand' => 'Lenovo',
             'price' => 57990, 'sale_price' => 49990, 'stock' => 25, 'rating' => 4.3,
             'description' => 'Slim Ryzen 7 laptop with 16GB RAM and rapid charge.'],
            ['name' => 'Apple MacBook Air M2', 'category' => $e, 'brand' => 'Apple',
             'price' => 114900, 'sale_price' => 99900, 'stock' => 15, 'rating' => 4.9,
             'description' => 'Ultra-thin laptop with M2 chip and 18-hour battery life.'],

            // ---- Electronics (Audio) ----
            ['name' => 'boAt Airdopes 141 TWS Earbuds', 'category' => $e, 'brand' => 'boAt',
             'price' => 2999, 'sale_price' => 1299, 'stock' => 300, 'rating' => 4.1,
             'description' => '42 hours playback, low latency Beast mode for gaming.'],
            ['name' => 'JBL Flip 6 Bluetooth Speaker', 'category' => $e, 'brand' => 'JBL',
             'price' => 12999, 'sale_price' => 8999, 'stock' => 60, 'rating' => 4.6,
             'description' => 'Bold JBL sound with IP67 waterproof rating.'],
            ['name' => 'Noise ColorFit Pro 5 Smartwatch', 'category' => $e, 'brand' => 'Noise',
             'price' => 5999, 'sale_price' => 3499, 'stock' => 200, 'rating' => 4.2,
             'description' => '1.85" AMOLED display, 7-day battery, 100+ sports modes.'],

            // ---- Electronics (Cameras & Accessories) ----
            ['name' => 'Canon EOS R50 Mirrorless Camera', 'category' => $e, 'brand' => 'Canon',
             'price' => 67995, 'sale_price' => 62995, 'stock' => 12, 'rating' => 4.7,
             'description' => '24.2MP mirrorless camera with 4K video and dual pixel AF.'],
            ['name' => 'SanDisk 256GB Ultra SD Card', 'category' => $e, 'brand' => 'SanDisk',
             'price' => 2799, 'sale_price' => 1899, 'stock' => 250, 'rating' => 4.5,
             'description' => '150MB/s read speeds, perfect for cameras and drones.'],
            ['name' => 'Anker 20W USB-C Fast Charger', 'category' => $e, 'brand' => 'Anker',
             'price' => 1899, 'sale_price' => 1249, 'stock' => 180, 'rating' => 4.6,
             'description' => 'Compact PD charger with PowerIQ for fast safe charging.'],

            // ---- Home & Kitchen (Appliances) ----
            ['name' => 'Philips Air Fryer HD9252', 'category' => $h, 'brand' => 'Philips',
             'price' => 10995, 'sale_price' => 7999, 'stock' => 45, 'rating' => 4.5,
             'description' => 'Fry with 90% less fat using Rapid Air technology.'],
            ['name' => 'Prestige Iris 750W Mixer Grinder', 'category' => $h, 'brand' => 'Prestige',
             'price' => 4295, 'sale_price' => 2899, 'stock' => 80, 'rating' => 4.3,
             'description' => '750W motor with 4 jars for wet and dry grinding.'],
            ['name' => 'Kent RO Water Purifier 8L', 'category' => $h, 'brand' => 'Kent',
             'price' => 16500, 'sale_price' => 13499, 'stock' => 35, 'rating' => 4.4,
             'description' => 'RO+UV+TDS mineral water purifier with 8L storage.'],

            // ---- Home & Kitchen (Cookware & Decor) ----
            ['name' => 'Hawkins Futura Non-Stick Tawa 28cm', 'category' => $h, 'brand' => 'Hawkins',
             'price' => 1895, 'sale_price' => 1450, 'stock' => 110, 'rating' => 4.6,
             'description' => 'Pioneering non-stick tawa for perfect dosas and parathas.'],
            ['name' => 'Home Centre Ceramic Vase Set', 'category' => $h, 'brand' => 'Home Centre',
             'price' => 1699, 'sale_price' => 999, 'stock' => 60, 'rating' => 4.2,
             'description' => 'Set of 3 matte ceramic vases in contemporary shapes.'],
            ['name' => 'Spaces Cushioned Bed Sheet Set', 'category' => $h, 'brand' => 'Spaces',
             'price' => 2499, 'sale_price' => 1649, 'stock' => 75, 'rating' => 4.3,
             'description' => 'Double bedsheet with 2 pillow covers, 186 TC cotton.'],

            // ---- Beauty (Skincare & Makeup) ----
            ['name' => 'Cetaphil Gentle Skin Cleanser 250ml', 'category' => $b, 'brand' => 'Cetaphil',
             'price' => 599, 'sale_price' => 499, 'stock' => 250, 'rating' => 4.7,
             'description' => 'Dermatologist-recommended cleanser for sensitive skin.'],
            ['name' => 'Minimalist 2% Salicylic Acid Serum', 'category' => $b, 'brand' => 'Minimalist',
             'price' => 599, 'sale_price' => 479, 'stock' => 180, 'rating' => 4.4,
             'description' => 'Targeted serum for acne-prone and oily skin.'],
            ['name' => 'Lakme 9to5 Primer + Matte Lipstick', 'category' => $b, 'brand' => 'Lakme',
             'price' => 650, 'sale_price' => 550, 'stock' => 300, 'rating' => 4.3,
             'description' => 'Matte lipstick with built-in primer, 8-hour stay.'],
            ['name' => 'The Derma Co Sunscreen SPF 50', 'category' => $b, 'brand' => 'Minimalist',
             'price' => 499, 'sale_price' => 399, 'stock' => 220, 'rating' => 4.5,
             'description' => 'No white cast broad-spectrum gel sunscreen.'],
            ['name' => 'Wow Skin Science Apple Cider Vinegar Shampoo', 'category' => $b, 'brand' => 'WOW',
             'price' => 699, 'sale_price' => 489, 'stock' => 190, 'rating' => 4.1,
             'description' => 'Dandruff-control shampoo with pure ACV and argan oil.'],

            // ---- Grocery (Staples & Snacks) ----
            ['name' => 'Daawat Rozana Basmati Rice 5kg', 'category' => 'Grocery', 'brand' => 'Daawat',
             'price' => 675, 'sale_price' => 549, 'stock' => 140, 'rating' => 4.3,
             'description' => 'Everyday super basmati rice with long slender grains.'],
            ['name' => 'Amul Butter 500g', 'category' => 'Grocery', 'brand' => 'Amul',
             'price' => 295, 'stock' => 400, 'rating' => 4.8,
             'description' => 'India\'s favourite butter, Utterly Amulicious.'],
            ['name' => 'Haldiram\'s Aloo Bhujia 400g', 'category' => 'Grocery', 'brand' => 'Haldiram\'s',
             'price' => 165, 'sale_price' => 148, 'stock' => 350, 'rating' => 4.5,
             'description' => 'Crispy potato bhujia, the perfect chai-time snack.'],
            ['name' => 'Nescafe Classic Coffee 200g', 'category' => 'Grocery', 'brand' => 'Nescafe',
             'price' => 599, 'sale_price' => 529, 'stock' => 200, 'rating' => 4.4,
             'description' => '100% pure instant coffee with rich aroma.'],
            ['name' => 'Fortune Chakki Fresh Atta 10kg', 'category' => 'Grocery', 'brand' => 'Fortune',
             'price' => 525, 'sale_price' => 449, 'stock' => 120, 'rating' => 4.2,
             'description' => 'Whole wheat chakki atta for soft rotis.'],
            ['name' => 'Dove Cream Beauty Bathing Bar (Pack of 8)', 'category' => 'Grocery', 'brand' => 'Dove',
             'price' => 448, 'sale_price' => 384, 'stock' => 260, 'rating' => 4.6,
             'description' => 'Moisturising cream bar for soft smooth skin.'],

            // ---- Sports (Cricket & Football) ----
            ['name' => 'GM Diamond Cricket Bat Kashmir Willow', 'category' => $s, 'brand' => 'GM',
             'price' => 3499, 'sale_price' => 2499, 'stock' => 40, 'rating' => 4.3,
             'description' => 'Powerful profile bat for tennis ball and leather play.'],
            ['name' => 'Adidas Champions League Football', 'category' => $s, 'brand' => 'Adidas',
             'price' => 2499, 'sale_price' => 1749, 'stock' => 55, 'rating' => 4.5,
             'description' => 'FIFA-quality match ball with seamless TSBE construction.'],
            ['name' => 'SG Cricket Kit Full Set', 'category' => $s, 'brand' => 'SG',
             'price' => 7999, 'sale_price' => 5999, 'stock' => 20, 'rating' => 4.4,
             'description' => 'Complete cricket kit: bat, pads, gloves, helmet, kit bag.'],

            // ---- Sports (Fitness & Outdoor) ----
            ['name' => 'Boldfit Yoga Mat 6mm Anti-Skid', 'category' => $s, 'brand' => 'Boldfit',
             'price' => 999, 'sale_price' => 599, 'stock' => 220, 'rating' => 4.2,
             'description' => 'Extra-thick cushioned yoga mat with carry strap.'],
            ['name' => 'Kore PVC Dumbbell Set 20kg', 'category' => $s, 'brand' => 'Kore',
             'price' => 2999, 'sale_price' => 2099, 'stock' => 65, 'rating' => 4.3,
             'description' => 'Home gym starter set with two dumbbells and rods.'],
            ['name' => 'Wildcraft HypaShell Trekking Jacket', 'category' => $s, 'brand' => 'Wildcraft',
             'price' => 4495, 'sale_price' => 3149, 'stock' => 45, 'rating' => 4.5,
             'description' => 'Waterproof windproof shell jacket for treks and hikes.'],
            ['name' => 'Cosco Shuttlecocks Nylon (Pack of 12)', 'category' => $s, 'brand' => 'Cosco',
             'price' => 899, 'sale_price' => 749, 'stock' => 130, 'rating' => 4.1,
             'description' => 'Durable nylon shuttles for practice and casual play.'],
        ];
    }
}
