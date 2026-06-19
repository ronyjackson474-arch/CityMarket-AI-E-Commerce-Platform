<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            ['name' => 'Nike', 'logo' => '👟', 'description' => 'Just Do It. Premium athletic footwear and apparel.'],
            ['name' => 'Adidas', 'logo' => '🎯', 'description' => 'Impossible is Nothing. Sports gear for champions.'],
            ['name' => 'Apple', 'logo' => '🍎', 'description' => 'Think Different. Premium technology products.'],
            ['name' => 'Samsung', 'logo' => '📱', 'description' => 'Innovation for everyone.'],
            ['name' => 'Sony', 'logo' => '🎮', 'description' => 'Make. Believe. Entertainment and electronics.'],
            ['name' => 'Puma', 'logo' => '🐆', 'description' => 'Forever Faster. Sports and lifestyle.'],
            ['name' => 'boAt', 'logo' => '🚤', 'description' => 'Plug Into Nirvana. Audio for India.'],
            ['name' => 'Mi', 'logo' => '📱', 'description' => 'Innovation for everyone. Smart devices.'],
            ['name' => 'Philips', 'logo' => '💡', 'description' => 'Innovation and you.'],
            ['name' => 'Himalaya', 'logo' => '🏔️', 'description' => 'Naturally healthy since 1930.'],
        ];

        foreach ($brands as $b) {
            Brand::updateOrCreate(
                ['slug' => Str::slug($b['name'])],
                array_merge($b, ['slug' => Str::slug($b['name']), 'is_active' => true])
            );
        }
    }
}
