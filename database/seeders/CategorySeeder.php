<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Fashion', 'icon' => '👗', 'description' => 'Clothing, footwear, and accessories for everyone.'],
            ['name' => 'Electronics', 'icon' => '📱', 'description' => 'Phones, laptops, gadgets, and accessories.'],
            ['name' => 'Home & Kitchen', 'icon' => '🏠', 'description' => 'Everything for your home and kitchen.'],
            ['name' => 'Beauty', 'icon' => '💄', 'description' => 'Skincare, makeup, and personal care.'],
            ['name' => 'Grocery', 'icon' => '🛒', 'description' => 'Daily essentials and groceries.'],
            ['name' => 'Sports', 'icon' => '⚽', 'description' => 'Sports gear and fitness equipment.'],
        ];

        $subCategories = [
            'Fashion' => ['Men', "Women's", 'Kids', 'Footwear', 'Bags'],
            'Electronics' => ['Mobiles', 'Laptops', 'Audio', 'Cameras', 'Accessories'],
            'Home & Kitchen' => ['Furniture', 'Cookware', 'Decor', 'Appliances'],
            'Beauty' => ['Skincare', 'Makeup', 'Hair Care', 'Fragrance'],
            'Grocery' => ['Staples', 'Snacks', 'Beverages', 'Personal Care'],
            'Sports' => ['Cricket', 'Football', 'Fitness', 'Outdoor'],
        ];

        foreach ($categories as $index => $cat) {
            $parent = Category::updateOrCreate(
                ['slug' => Str::slug($cat['name'])],
                [
                    'parent_id' => null,
                    'name' => $cat['name'],
                    'slug' => Str::slug($cat['name']),
                    'description' => $cat['description'],
                    'icon' => $cat['icon'],
                    'image' => 'https://picsum.photos/seed/' . Str::slug($cat['name']) . '/400/400',
                    'is_active' => true,
                    'position' => $index,
                ]
            );

            $position = 0;
            foreach (($subCategories[$cat['name']] ?? []) as $subName) {
                $subSlug = Str::slug($cat['name'] . '-' . $subName);
                Category::updateOrCreate(
                    ['slug' => $subSlug],
                    [
                        'parent_id' => $parent->id,
                        'name' => $subName,
                        'slug' => $subSlug,
                        'description' => "{$subName} under {$cat['name']}",
                        'icon' => $cat['icon'],
                        'image' => 'https://picsum.photos/seed/' . $subSlug . '/400/400',
                        'is_active' => true,
                        'position' => $position++,
                    ]
                );
            }
        }
    }
}
