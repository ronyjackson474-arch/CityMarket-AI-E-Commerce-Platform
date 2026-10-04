<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Vendor earnings ledger + withdrawal requests.
     */
    public function up(): void
    {
        Schema::create('vendor_withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('method')->default('bank_transfer'); // bank_transfer | upi
            $table->string('payout_detail'); // bank account / UPI id snapshot
            $table->string('status')->default('pending'); // pending | approved | paid | rejected
            $table->text('admin_note')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['vendor_id', 'status']);
        });

        Schema::create('vendor_earnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained('order_items')->cascadeOnDelete();
            $table->decimal('gross', 12, 2);      // price * qty
            $table->decimal('commission', 12, 2); // platform cut
            $table->decimal('net', 12, 2);        // vendor keeps this
            $table->string('status')->default('available'); // available | withdrawn
            $table->timestamps();

            $table->unique('order_item_id');
            $table->index(['vendor_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_earnings');
        Schema::dropIfExists('vendor_withdrawals');
    }
};
