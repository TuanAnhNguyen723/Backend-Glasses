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
        if (!Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 20)->nullable()->after('password');
            }
            if (!Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('users', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable()->after('avatar');
            }
            if (!Schema::hasColumn('users', 'gender')) {
                $table->string('gender', 20)->nullable()->after('date_of_birth');
            }
            if (!Schema::hasColumn('users', 'is_premium')) {
                $table->boolean('is_premium')->default(false)->after('gender');
            }
            if (!Schema::hasColumn('users', 'premium_since')) {
                $table->timestamp('premium_since')->nullable()->after('is_premium');
            }
            if (!Schema::hasColumn('users', 'preferred_frame_style')) {
                $table->string('preferred_frame_style', 50)->nullable()->after('premium_since');
            }
            if (!Schema::hasColumn('users', 'marketing_newsletter')) {
                $table->boolean('marketing_newsletter')->default(false)->after('preferred_frame_style');
            }
            if (!Schema::hasColumn('users', 'prescription_reminders')) {
                $table->boolean('prescription_reminders')->default(false)->after('marketing_newsletter');
            }
            if (!Schema::hasColumn('users', 'language')) {
                $table->string('language', 10)->default('vi')->after('prescription_reminders');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'phone', 'avatar', 'date_of_birth', 'gender', 'is_premium',
                'premium_since', 'preferred_frame_style', 'marketing_newsletter',
                'prescription_reminders', 'language',
            ];
            foreach ($columns as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
