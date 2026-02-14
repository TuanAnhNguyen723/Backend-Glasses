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
        if (!Schema::hasTable('order_items')) {
            Schema::create('order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
                $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
                $table->string('product_name');
                $table->string('product_color_name', 100)->nullable();
                $table->string('lens_option_name', 100)->nullable();
                $table->unsignedInteger('quantity')->default(1);
                $table->decimal('unit_price', 10, 2);
                $table->decimal('total_price', 10, 2);
                $table->string('product_image_url', 500)->nullable();
                $table->timestamps();

                $table->index('order_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
