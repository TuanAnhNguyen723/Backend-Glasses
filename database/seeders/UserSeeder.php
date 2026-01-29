<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => Hash::make('password123'),
                'phone' => '0901234567',
                'gender' => 'male',
                'is_premium' => true,
                'premium_since' => now()->subMonths(3),
                'preferred_frame_style' => 'aviator',
                'marketing_newsletter' => true,
                'prescription_reminders' => true,
                'language' => 'vi',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Nguyễn Văn An',
                'email' => 'nguyenvanan@example.com',
                'password' => Hash::make('password123'),
                'phone' => '0912345678',
                'date_of_birth' => '1990-05-15',
                'gender' => 'male',
                'is_premium' => false,
                'preferred_frame_style' => 'wayfarer',
                'marketing_newsletter' => true,
                'prescription_reminders' => false,
                'language' => 'vi',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Trần Thị Bình',
                'email' => 'tranthibinh@example.com',
                'password' => Hash::make('password123'),
                'phone' => '0923456789',
                'date_of_birth' => '1995-08-20',
                'gender' => 'female',
                'is_premium' => true,
                'premium_since' => now()->subMonths(1),
                'preferred_frame_style' => 'cat-eye',
                'marketing_newsletter' => true,
                'prescription_reminders' => true,
                'language' => 'vi',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Lê Văn Cường',
                'email' => 'levancuong@example.com',
                'password' => Hash::make('password123'),
                'phone' => '0934567890',
                'date_of_birth' => '1988-12-10',
                'gender' => 'male',
                'is_premium' => false,
                'preferred_frame_style' => 'round',
                'marketing_newsletter' => false,
                'prescription_reminders' => true,
                'language' => 'vi',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Phạm Thị Dung',
                'email' => 'phamthidung@example.com',
                'password' => Hash::make('password123'),
                'phone' => '0945678901',
                'date_of_birth' => '1992-03-25',
                'gender' => 'female',
                'is_premium' => false,
                'preferred_frame_style' => 'square',
                'marketing_newsletter' => true,
                'prescription_reminders' => false,
                'language' => 'vi',
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                $user
            );
        }

        $this->command->info('Seeded ' . count($users) . ' users.');
        $this->command->info('Default password for all users: password123');
    }
}
