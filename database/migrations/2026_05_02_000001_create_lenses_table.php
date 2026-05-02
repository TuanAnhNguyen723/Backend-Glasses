<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('lenses')) {
            Schema::create('lenses', function (Blueprint $table) {
                $table->id();
                $table->string('sku', 100)->unique();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->string('lens_type', 50)->default('myopia');
                $table->decimal('base_price', 10, 2)->default(0);
                $table->unsignedInteger('stock_quantity')->default(0);
                $table->boolean('requires_prescription')->default(true);
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();

                $table->index('lens_type');
                $table->index('is_active');
                $table->index('sort_order');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lenses');
    }
};
