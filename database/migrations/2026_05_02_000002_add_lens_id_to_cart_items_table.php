<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cart_items') && !Schema::hasColumn('cart_items', 'lens_id')) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->foreignId('lens_id')->nullable()->after('product_color_id')->constrained('lenses')->nullOnDelete();
                $table->index(['user_id', 'product_id', 'product_color_id', 'lens_id'], 'cart_items_user_product_color_lens_id_index');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('cart_items') && Schema::hasColumn('cart_items', 'lens_id')) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->dropIndex('cart_items_user_product_color_lens_id_index');
                $table->dropConstrainedForeignId('lens_id');
            });
        }
    }
};
