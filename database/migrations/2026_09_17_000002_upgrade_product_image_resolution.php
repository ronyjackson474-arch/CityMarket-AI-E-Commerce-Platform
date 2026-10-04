<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Upgrade seeded picsum product image URLs from 600x600 to 1200x1200
     * so images render sharp on large cards / detail / lightbox (2x retina).
     */
    public function up(): void
    {
        DB::table('product_images')
            ->where('image_path', 'like', '%picsum.photos%')
            ->update([
                'image_path' => DB::raw("REGEXP_REPLACE(image_path, '/600/600$', '/1200/1200')"),
            ]);
    }

    public function down(): void
    {
        DB::table('product_images')
            ->where('image_path', 'like', '%picsum.photos%')
            ->update([
                'image_path' => DB::raw("REGEXP_REPLACE(image_path, '/1200/1200$', '/600/600')"),
            ]);
    }
};
