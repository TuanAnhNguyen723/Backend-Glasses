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
        if (! Schema::hasTable('user_promo_codes')) {
            Schema::create('user_promo_codes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('promo_code_id')->constrained('promo_codes')->cascadeOnDelete();
                $table->timestamp('claimed_at')->nullable();
                $table->timestamp('used_at')->nullable();
                $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
                $table->timestamps();

                $table->unique(['user_id', 'promo_code_id']);
                $table->index('claimed_at');
                $table->index('used_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_promo_codes');
    }
};
