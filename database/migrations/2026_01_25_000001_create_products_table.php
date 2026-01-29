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
        if (!Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->id();
                $table->string('sku')->unique();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->text('short_description')->nullable();
                $table->decimal('base_price', 10, 2);
                $table->decimal('compare_price', 10, 2)->nullable();
                $table->foreignId('category_id')->constrained('categories')->onDelete('restrict');
                $table->string('frame_shape');
                $table->string('material')->nullable();
                $table->string('size')->nullable();
                $table->string('bridge')->nullable();
                $table->integer('stock_quantity')->default(0);
                $table->integer('low_stock_threshold')->default(10);
                $table->decimal('rating_average', 3, 2)->default(0);
                $table->integer('rating_count')->default(0);
                $table->integer('review_count')->default(0);
                $table->string('badge')->nullable();
                $table->boolean('is_featured')->default(false);
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->string('meta_title')->nullable();
                $table->text('meta_description')->nullable();
                $table->timestamps();
                
                // Indexes
                $table->index('category_id');
                $table->index('sku');
                $table->index('slug');
                $table->index('is_active');
                $table->index('is_featured');
                $table->index('frame_shape');
                $table->index('sort_order');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
