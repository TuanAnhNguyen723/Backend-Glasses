<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lenses') && !Schema::hasColumn('lenses', 'requires_prescription')) {
            Schema::table('lenses', function (Blueprint $table) {
                $table->boolean('requires_prescription')->default(true)->after('stock_quantity');
            });
        }
    }

    public function down(): void
    {
        // Cột này hiện thuộc migration create_lenses_table. Giữ no-op để rollback từng bước
        // không vô tình xóa cột khi migration tạo bảng vẫn đang được xem là đã chạy.
    }
};
