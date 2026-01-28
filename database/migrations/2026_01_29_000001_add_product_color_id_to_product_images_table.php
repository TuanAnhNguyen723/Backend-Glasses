<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('product_images')) {
            return;
        }

        if (!Schema::hasColumn('product_images', 'product_color_id')) {
            Schema::table('product_images', function (Blueprint $table) {
                $table->unsignedBigInteger('product_color_id')->nullable()->after('product_id');
                $table->index('product_color_id', 'product_images_product_color_id_index');
            });
        }

        // Add FK only if product_colors table exists (repo migrations may be incomplete)
        if (Schema::hasTable('product_colors')) {
            Schema::table('product_images', function (Blueprint $table) {
                // Avoid duplicate FK creation across environments
                $table->foreign('product_color_id', 'product_images_product_color_id_fk')
                    ->references('id')
                    ->on('product_colors')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('product_images') || !Schema::hasColumn('product_images', 'product_color_id')) {
            return;
        }

        Schema::table('product_images', function (Blueprint $table) {
            // Drop FK if exists (safe in most DBs even if missing name will error; keep named FK above)
            try {
                $table->dropForeign('product_images_product_color_id_fk');
            } catch (\Throwable $e) {
                // ignore
            }

            try {
                $table->dropIndex('product_images_product_color_id_index');
            } catch (\Throwable $e) {
                // ignore
            }

            $table->dropColumn('product_color_id');
        });
    }
};

