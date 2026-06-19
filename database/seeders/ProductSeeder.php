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

            // Images
            $product->images()->delete();
            foreach (($data['images'] ?? [1]) as $i => $seed) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => "https://picsum.photos/seed/{$slug}-{$seed}/600/600",
                    'is_primary' => $i === 0,
                    'position' => $i,
                ]);
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
             'featured' => true, 'images' => [1, 2, 3],
             'description' => 'The most powerful iPhone ever with the A17 Pro chip, titanium design, and pro camera system.',
             'specifications' => ['Display' => '6.7" Super Retina XDR', 'Chip' => 'A17 Pro', 'Camera' => '48MP Triple', 'Battery' => '29 hours video']],
            ['name' => 'Samsung Galaxy S24 Ultra', 'category' => $e, 'brand' => 'Samsung',
             'price' => 129999, 'sale_price' => 109999, 'stock' => 40, 'rating' => 4.7, 'review_count' => 280, 'flash_deal' => true,
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
             'price' => 2990, 'sale_price' => 1499, 'stock' => 200, 'rating' => 4.2, 'review_count' => 5400, 'flash_deal' => true,
             'description' => 'Immersive audio with up to 15 hours playback, lightweight and comfortable.',
             'specifications' => ['Battery' => '15 hours', 'Connectivity' => 'Bluetooth 5.0']],
            ['name' => 'Sony WH-1000XM5 Wireless Headphones', 'category' => $e, 'brand' => 'Sony',
             'price' => 29990, 'sale_price' => 24990, 'stock' => 30, 'rating' => 4.8, 'featured' => true,
             'description' => 'Industry-leading noise cancellation with crystal-clear hands-free calls.'],
            ['name' => 'Apple AirPods Pro (2nd Gen)', 'category' => $e, 'brand' => 'Apple',
             'price' => 26900, 'sale_price' => 22900, 'stock' => 50, 'rating' => 4.7,
             'description' => 'Adaptive Audio, Active Noise Cancellation, and up to 6 hours of listening time.'],
            ['name' => 'JBL Flip 6 Portable Speaker', 'category' => $e, 'brand' => 'Sony',
             'price' => 9999, 'sale_price' => 7999, 'stock' => 80, 'rating' => 4.6,
             'description' => 'Bold JBL Original Pro Sound in a waterproof portable speaker.'],

            // ---- Electronics / Laptops ----
            ['name' => 'MacBook Air M3 13-inch', 'category' => $e, 'brand' => 'Apple',
             'price' => 114900, 'sale_price' => 109900, 'stock' => 20, 'rating' => 4.9, 'featured' => true,
             'description' => 'Strikingly thin, blazing fast, and with all-day battery life powered by the M3 chip.'],
            ['name' => 'Dell XPS 13 Plus', 'category' => $e, 'brand' => 'Samsung',
             'price' => 99999, 'stock' => 15, 'rating' => 4.4,
             'description' => 'Stunning edge-to-edge display with Intel 12th Gen processors.'],

            // ---- Fashion / Footwear ----
            ['name' => 'Nike Air Max 270 Running Shoes', 'category' => $f, 'brand' => 'Nike',
             'price' => 8995, 'sale_price' => 5995, 'stock' => 120, 'rating' => 4.6, 'review_count' => 2100, 'featured' => true, 'flash_deal' => true,
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
             'price' => 3295, 'sale_price' => 2199, 'stock' => 150, 'rating' => 4.4, 'review_count' => 3400, 'flash_deal' => true,
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
             'price' => 12995, 'sale_price' => 8499, 'stock' => 70, 'rating' => 4.6, 'review_count' => 1200, 'featured' => true,
             'description' => 'Rapid Air Technology cooks food with little to no oil for healthy meals.',
             'specifications' => ['Capacity' => '4.1L', 'Power' => '1400W']],
            ['name' => 'Prestige Pressure Cooker 5L', 'category' => $h, 'brand' => 'Philips',
             'price' => 1599, 'sale_price' => 1099, 'stock' => 250, 'rating' => 4.4,
             'description' => 'Durable aluminium pressure cooker for fast, healthy cooking.'],
            ['name' => 'Milton Thermosteel Flask 1L', 'category' => $h, 'brand' => 'Philips',
             'price' => 999, 'sale_price' => 649, 'stock' => 400, 'rating' => 4.5, 'flash_deal' => true,
             'description' => 'Double-wall vacuum insulation keeps liquids hot/cold for 24 hours.'],
            ['name' => 'Sony Bravia 55" 4K Smart TV', 'category' => $h, 'brand' => 'Sony',
             'price' => 89990, 'sale_price' => 69990, 'stock' => 15, 'rating' => 4.7, 'featured' => true,
             'description' => '4K HDR with Google TV, Dolby Vision, and immersive sound.'],

            // ---- Beauty ----
            ['name' => 'Himalaya Neem Face Wash 200ml', 'category' => $b, 'brand' => 'Himalaya',
             'price' => 295, 'sale_price' => 195, 'stock' => 500, 'rating' => 4.5, 'review_count' => 8900, 'flash_deal' => true,
             'description' => 'Neem and turmeric face wash for clear, pimple-free skin.'],
            ['name' => 'Lakme 9 to 5 Primer + Matte Lipstick', 'category' => $b, 'brand' => 'Himalaya',
             'price' => 450, 'sale_price' => 349, 'stock' => 300, 'rating' => 4.3,
             'description' => 'Long-lasting matte lipstick with built-in primer for smooth application.'],
            ['name' => 'Nivea Body Lotion 400ml', 'category' => $b, 'brand' => 'Himalaya',
             'price' => 399, 'sale_price' => 299, 'stock' => 400, 'rating' => 4.4,
             'description' => 'Nourishing shea smooth body lotion for 48-hour moisturization.'],

            // ---- Sports ----
            ['name' => 'SG Cricket Bat English Willow', 'category' => $s, 'brand' => 'Puma',
             'price' => 8999, 'sale_price' => 5999, 'stock' => 30, 'rating' => 4.6, 'featured' => true,
             'description' => 'Professional-grade English willow cricket bat with excellent balance.'],
            ['name' => 'Nike Mercurial Football Boots', 'category' => $s, 'brand' => 'Nike',
             'price' => 6995, 'sale_price' => 4495, 'stock' => 60, 'rating' => 4.5, 'flash_deal' => true,
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
        ];
    }
}
