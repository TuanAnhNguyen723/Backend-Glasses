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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('user_id')->nullable();

            $table->unsignedTinyInteger('rating')->default(0);
            $table->text('comment')->nullable();
            $table->boolean('is_approved')->default(false);

            $table->timestamps();

            $table->index('product_id');
            $table->index('user_id');
            $table->index('is_approved');

            // Optional foreign keys (commented out to avoid issues if tables not yet present)
            // Uncomment if you want strict FK constraints:
            // $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};

