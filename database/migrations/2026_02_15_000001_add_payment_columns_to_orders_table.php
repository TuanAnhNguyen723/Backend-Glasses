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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_status', 32)->default('pending')->after('notes'); // pending, paid, refunded, failed
            $table->string('payment_method', 64)->nullable()->after('payment_status');   // cod, bank_transfer, momo, vnpay, ...
            $table->string('payment_reference', 255)->nullable()->after('payment_method'); // mã giao dịch từ cổng thanh toán
            $table->timestamp('paid_at')->nullable()->after('payment_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'payment_method', 'payment_reference', 'paid_at']);
        });
    }
};
