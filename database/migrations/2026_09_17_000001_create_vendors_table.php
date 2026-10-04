<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('shop_name');
            $table->string('slug')->unique();
            $table->string('logo_url')->nullable();
            $table->string('banner_url')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('pending')->index(); // pending | approved | suspended
            $table->decimal('rating', 3, 2)->default(0);
            $table->unsignedTinyInteger('commission_pct')->default(10);
            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('vendor_id')->nullable()->after('brand_id')->constrained()->nullOnDelete();
            $table->index(['vendor_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('vendor_id');
            $table->dropIndex(['vendor_id', 'is_active']);
        });
        Schema::dropIfExists('vendors');
    }
};
