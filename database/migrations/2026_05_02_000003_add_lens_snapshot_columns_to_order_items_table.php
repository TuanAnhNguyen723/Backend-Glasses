<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('order_items')) {
            Schema::table('order_items', function (Blueprint $table) {
                if (!Schema::hasColumn('order_items', 'lens_name')) {
                    $table->string('lens_name', 100)->nullable()->after('product_color_name');
                }
                if (!Schema::hasColumn('order_items', 'lens_sku')) {
                    $table->string('lens_sku', 100)->nullable()->after('lens_name');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('order_items')) {
            Schema::table('order_items', function (Blueprint $table) {
                if (Schema::hasColumn('order_items', 'lens_sku')) {
                    $table->dropColumn('lens_sku');
                }
                if (Schema::hasColumn('order_items', 'lens_name')) {
                    $table->dropColumn('lens_name');
                }
            });
        }
    }
};
