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
        if (!Schema::hasTable('cart_items')) {
            Schema::create('cart_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
                $table->string('session_id', 100)->nullable()->index();
                $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
                $table->foreignId('product_color_id')->nullable()->constrained('product_colors')->onDelete('cascade');
                $table->foreignId('lens_option_id')->nullable()->constrained('lens_options')->onDelete('cascade');
                $table->unsignedInteger('quantity')->default(1);
                $table->decimal('unit_price', 10, 2);
                $table->timestamps();

                $table->index(['user_id', 'product_id', 'product_color_id', 'lens_option_id'], 'cart_items_user_product_color_lens_index');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
