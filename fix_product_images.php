<?php
/**
 * One-off fixer (ticket #14584): replace random picsum images with
 * category-appropriate real product photos (Unsplash, stable CDN).
 * Run: php artisan tinker --execute="require 'fix_product_images.php';"
 */

use Illuminate\Support\Facades\DB;

// Unsplash photo IDs chosen per product type (stable images.unsplash.com URLs)
$map = [
    // Fashion
    'us-polo-assn-checked-casual-shirt' => 'photo-1596755094514-f87e34085b2c',   // checked shirt
    'puma-solid-polo-t-shirt'           => 'photo-1586363104862-3a5e2ab60d99',   // polo t-shirt
    'jack-jones-bomber-jacket'          => 'photo-1551028719-00167b16eac5',      // bomber jacket
    'libas-printed-anarkali-kurta'      => 'photo-1583391730556-575cef4c7e05',   // indian ethnic kurta
    'only-high-rise-skinny-jeans'       => 'photo-1541099649105-f69ad21f3246',   // skinny jeans
    'vero-moda-floral-wrap-dress'       => 'photo-1572804013309-59a88b7e92f1',   // floral dress
    'biba-straight-cotton-suit-set'     => 'photo-1610030469983-98e550d6193c',   // salwar suit
    'adidas-ultraboost-light'           => 'photo-1595950653106-6c9ebd614d3a',   // running shoes
    'campus-running-shoes'              => 'photo-1606107557195-0e29a4b5b4aa',   // sports shoes
    'bata-formal-leather-shoes'         => 'photo-1614252369475-531eba835eb1',   // formal shoes
    'crocs-classic-clog'                => 'photo-1611080626919-7cf5a9dbab5b',   // clogs
    'american-tourister-laptop-backpack-32l' => 'photo-1553062407-98eeb64c6a62', // laptop backpack
    'lavie-womens-tote-handbag'         => 'photo-1584917865442-de89df76afd3',   // women handbag

    // Electronics
    'hp-pavilion-x360-touch-laptop'     => 'photo-1611186871348-b1ce696e52c9',   // convertible laptop
    'lenovo-ideapad-slim-5'             => 'photo-1602080858428-57174f9431cf',   // slim laptop
    'apple-macbook-air-m2'              => 'photo-1517336714731-489689fd1ca8',   // macbook
    'boat-airdopes-141-tws-earbuds'     => 'photo-1590658268037-6bf12165a8df',   // tws earbuds
    'jbl-flip-6-bluetooth-speaker'      => 'photo-1608043152269-423dbba4e7e1',   // bluetooth speaker
    'noise-colorfit-pro-5-smartwatch'   => 'photo-1546868871-7041f2a55e12',      // smartwatch
    'canon-eos-r50-mirrorless-camera'   => 'photo-1516035069371-29a1b244cc32',   // mirrorless camera
    'sandisk-256gb-ultra-sd-card'       => 'photo-1591488320449-011701bb6704',   // sd card
    'anker-20w-usb-c-fast-charger'      => 'photo-1583863788434-e58a36330cf0',   // charger

    // Home & Kitchen
    'philips-air-fryer-hd9252'          => 'photo-1626074353765-517a681e40be',   // air fryer
    'prestige-iris-750w-mixer-grinder'  => 'photo-1574269909862-7e1d70bb8078',   // mixer grinder
    'kent-ro-water-purifier-8l'         => 'photo-1560179707-f14e90ef3623',      // water purifier/appliance
    'hawkins-futura-non-stick-tawa-28cm'=> 'photo-1585637071663-799845ad2042',   // tawa pan
    'home-centre-ceramic-vase-set'      => 'photo-1578500494198-246f612d3b3d',   // ceramic vases
    'spaces-cushioned-bed-sheet-set'    => 'photo-1522771739844-6a9f6d5f14af',   // bed sheets

    // Beauty
    'cetaphil-gentle-skin-cleanser-250ml'    => 'photo-1556228720-195a672e8a03', // cleanser bottle
    'minimalist-2-salicylic-acid-serum'      => 'photo-1620916566398-39f1143ab7be', // serum
    'lakme-9to5-primer-matte-lipstick'       => 'photo-1586495777744-4413f21062fa', // lipstick
    'the-derma-co-sunscreen-spf-50'          => 'photo-1556228578-8c89e6adf883',   // sunscreen
    'wow-skin-science-apple-cider-vinegar-shampoo' => 'photo-1631730486572-226d1f595b68', // shampoo

    // Grocery
    'daawat-rozana-basmati-rice-5kg'    => 'photo-1586201375761-83865001e31c',    // rice bag
    'amul-butter-500g'                  => 'photo-1589985270826-4b7bb135bc9d',    // butter
    'haldirams-aloo-bhujia-400g'        => 'photo-1613919113640-25732ec5e61f',    // namkeen snacks
    'nescafe-classic-coffee-200g'       => 'photo-1559056199-641a0ac8b55e',       // coffee jar
    'fortune-chakki-fresh-atta-10kg'    => 'photo-1574323347407-f5e1ad6d020b',    // wheat flour
    'dove-cream-beauty-bathing-bar-pack-of-8' => 'photo-1600857544200-b2f666a9a2ec', // soap bars

    // Sports
    'gm-diamond-cricket-bat-kashmir-willow' => 'photo-1531415074968-036ba1b575da', // cricket bat
    'adidas-champions-league-football'      => 'photo-1574629810360-7efbbe195018', // football
    'sg-cricket-kit-full-set'               => 'photo-1531415074968-036ba1b575da', // cricket gear
    'boldfit-yoga-mat-6mm-anti-skid'        => 'photo-1592432678016-e910b452f9a2', // yoga mat
    'kore-pvc-dumbbell-set-20kg'            => 'photo-1638536532686-d610adfc8e5c', // dumbbells
    'wildcraft-hypashell-trekking-jacket'   => 'photo-1544923246-77307dd654cb',    // trekking jacket
    'cosco-shuttlecocks-nylon-pack-of-12'   => 'photo-1626224583764-f87db24ac4ea', // badminton shuttle
];

$updated = 0; $missing = [];
foreach ($map as $slug => $photoId) {
    $product = App\Models\Product::where('slug', $slug)->first();
    if (! $product) { $missing[] = $slug; continue; }

    $url = "https://images.unsplash.com/{$photoId}?w=1200&h=1200&fit=crop&q=80&auto=format";

    DB::table('product_images')
        ->where('product_id', $product->id)
        ->where('image_path', 'like', '%picsum%')
        ->update(['image_path' => $url, 'updated_at' => now()]);
    $updated++;
}

echo "Updated: {$updated} products" . PHP_EOL;
if ($missing) echo "Missing products: " . implode(', ', $missing) . PHP_EOL;

// Any picsum left?
echo "Picsum remaining: " . DB::table('product_images')->where('image_path', 'like', '%picsum%')->count() . PHP_EOL;
