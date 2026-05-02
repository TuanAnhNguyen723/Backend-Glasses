<?php

namespace Database\Seeders;

use App\Models\Lens;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LensSeeder extends Seeder
{
    public function run(): void
    {
        $lenses = [
            [
                'sku' => 'LENS-CAN-001',
                'name' => 'Lens cận cơ bản',
                'description' => 'Lens cận tiêu chuẩn, phù hợp nhu cầu sử dụng hằng ngày.',
                'lens_type' => 'myopia',
                'base_price' => 300000,
                'stock_quantity' => 100,
                'requires_prescription' => true,
                'sort_order' => 1,
            ],
            [
                'sku' => 'LENS-VIEN-001',
                'name' => 'Lens viễn cơ bản',
                'description' => 'Lens viễn tiêu chuẩn cho khách cần hỗ trợ nhìn gần.',
                'lens_type' => 'hyperopia',
                'base_price' => 350000,
                'stock_quantity' => 80,
                'requires_prescription' => true,
                'sort_order' => 2,
            ],
            [
                'sku' => 'LENS-BLUE-001',
                'name' => 'Lens chống ánh sáng xanh',
                'description' => 'Lens hỗ trợ giảm ánh sáng xanh khi làm việc với màn hình.',
                'lens_type' => 'blue_light',
                'base_price' => 500000,
                'stock_quantity' => 70,
                'requires_prescription' => false,
                'sort_order' => 3,
            ],
            [
                'sku' => 'LENS-PHOTO-001',
                'name' => 'Lens đổi màu',
                'description' => 'Lens tự đổi màu khi ra nắng, phù hợp sử dụng trong nhà và ngoài trời.',
                'lens_type' => 'photochromic',
                'base_price' => 800000,
                'stock_quantity' => 50,
                'requires_prescription' => false,
                'sort_order' => 4,
            ],
        ];

        foreach ($lenses as $lens) {
            $slug = Str::slug($lens['name']);
            $existing = Lens::query()
                ->where('sku', $lens['sku'])
                ->orWhere('slug', $slug)
                ->first();

            if ($existing) {
                $existing->update(array_merge($lens, [
                    'slug' => $existing->slug ?: $slug,
                    'is_active' => true,
                ]));
                continue;
            }

            Lens::create(array_merge($lens, [
                'slug' => $slug,
                'is_active' => true,
            ]));
        }

        $this->command->info('Seeded ' . count($lenses) . ' lenses.');
    }
}
