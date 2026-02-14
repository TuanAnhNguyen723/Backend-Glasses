<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->id();
                $table->string('order_number', 32)->unique();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('status', 32)->default('pending'); // pending, confirmed, processing, shipped, delivered, cancelled
                $table->decimal('subtotal', 12, 2)->default(0);
                $table->decimal('tax_amount', 12, 2)->default(0);
                $table->decimal('shipping_amount', 12, 2)->default(0);
                $table->decimal('discount_amount', 12, 2)->default(0);
                $table->decimal('total_amount', 12, 2)->default(0);
                $table->string('promo_code', 64)->nullable();
                $table->string('shipping_name');
                $table->string('shipping_phone', 20);
                $table->string('shipping_email');
                $table->text('shipping_address');
                $table->string('shipping_city', 100)->nullable();
                $table->string('shipping_postal_code', 20)->nullable();
                $table->string('shipping_country', 100)->default('Vietnam');
                $table->string('tracking_number', 100)->nullable();
                $table->date('estimated_delivery_date')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index('user_id');
                $table->index('status');
                $table->index('order_number');
                $table->index('created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
