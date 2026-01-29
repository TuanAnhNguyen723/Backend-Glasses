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
        if (!Schema::hasTable('product_colors')) {
            Schema::create('product_colors', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
                $table->string('name');
                $table->string('hex_code', 7); // #RRGGBB format
                $table->decimal('price_adjustment', 10, 2)->default(0);
                $table->integer('stock_quantity')->default(0);
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
                
                // Indexes
                $table->index('product_id');
                $table->index('is_active');
                $table->index('sort_order');
                $table->index('hex_code');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_colors');
    }
};
