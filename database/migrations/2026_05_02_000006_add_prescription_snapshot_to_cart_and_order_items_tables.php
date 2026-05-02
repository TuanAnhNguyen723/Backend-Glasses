<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cart_items')) {
            Schema::table('cart_items', function (Blueprint $table) {
                if (!Schema::hasColumn('cart_items', 'prescription_type')) {
                    $table->string('prescription_type', 50)->nullable()->after('lens_id');
                }
                if (!Schema::hasColumn('cart_items', 'prescription_data')) {
                    $table->json('prescription_data')->nullable()->after('prescription_type');
                }
                if (!Schema::hasColumn('cart_items', 'prescription_hash')) {
                    $table->string('prescription_hash', 64)->nullable()->after('prescription_data');
                    $table->index('prescription_hash', 'cart_items_prescription_hash_index');
                }
            });
        }

        if (Schema::hasTable('order_items')) {
            Schema::table('order_items', function (Blueprint $table) {
                if (!Schema::hasColumn('order_items', 'prescription_type')) {
                    $table->string('prescription_type', 50)->nullable()->after('lens_sku');
                }
                if (!Schema::hasColumn('order_items', 'prescription_data')) {
                    $table->json('prescription_data')->nullable()->after('prescription_type');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('cart_items')) {
            Schema::table('cart_items', function (Blueprint $table) {
                if (Schema::hasColumn('cart_items', 'prescription_hash')) {
                    $table->dropIndex('cart_items_prescription_hash_index');
                    $table->dropColumn('prescription_hash');
                }
                if (Schema::hasColumn('cart_items', 'prescription_data')) {
                    $table->dropColumn('prescription_data');
                }
                if (Schema::hasColumn('cart_items', 'prescription_type')) {
                    $table->dropColumn('prescription_type');
                }
            });
        }

        if (Schema::hasTable('order_items')) {
            Schema::table('order_items', function (Blueprint $table) {
                if (Schema::hasColumn('order_items', 'prescription_data')) {
                    $table->dropColumn('prescription_data');
                }
                if (Schema::hasColumn('order_items', 'prescription_type')) {
                    $table->dropColumn('prescription_type');
                }
            });
        }
    }
};
