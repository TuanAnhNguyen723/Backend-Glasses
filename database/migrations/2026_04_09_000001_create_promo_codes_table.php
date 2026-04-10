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
        if (! Schema::hasTable('promo_codes')) {
            Schema::create('promo_codes', function (Blueprint $table) {
                $table->id();
                $table->string('code', 64)->unique();
                $table->string('name', 255)->nullable();
                $table->string('scope', 20)->default('all_products'); // all_products | product
                $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
                $table->string('discount_type', 20)->default('percent'); // percent | fixed
                $table->decimal('discount_value', 12, 2);
                $table->decimal('min_order_amount', 12, 2)->default(0);
                $table->decimal('max_discount_amount', 12, 2)->nullable();
                $table->unsignedInteger('usage_limit')->nullable();
                $table->unsignedInteger('used_count')->default(0);
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->boolean('is_active')->default(true);
                $table->text('description')->nullable();
                $table->timestamps();

                $table->index('scope');
                $table->index('product_id');
                $table->index('is_active');
                $table->index('ends_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promo_codes');
    }
};
