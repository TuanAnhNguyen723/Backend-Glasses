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
        if (Schema::hasTable('brands')) {
            Schema::table('brands', function (Blueprint $table) {
                // Change logo_url from string to text to support longer URLs
                if (Schema::hasColumn('brands', 'logo_url')) {
                    $table->text('logo_url')->nullable()->change();
                }
                // Also increase website_url length
                if (Schema::hasColumn('brands', 'website_url')) {
                    $table->string('website_url', 500)->nullable()->change();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('brands')) {
            Schema::table('brands', function (Blueprint $table) {
                if (Schema::hasColumn('brands', 'logo_url')) {
                    $table->string('logo_url')->nullable()->change();
                }
                if (Schema::hasColumn('brands', 'website_url')) {
                    $table->string('website_url')->nullable()->change();
                }
            });
        }
    }
};
