<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Gọng Nam',
                'slug' => 'gong-nam',
                'description' => 'Gọng kính phù hợp phong cách nam',
                'parent_id' => null,
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Gọng Nữ',
                'slug' => 'gong-nu',
                'description' => 'Gọng kính phù hợp phong cách nữ',
                'parent_id' => null,
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Gọng Unisex',
                'slug' => 'gong-unisex',
                'description' => 'Gọng kính trung tính, phù hợp cho cả nam và nữ',
                'parent_id' => null,
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Kính Mát',
                'slug' => 'kinh-mat',
                'description' => 'Kính mát thời trang với khả năng chống tia UV',
                'parent_id' => null,
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Gọng Trẻ Em',
                'slug' => 'gong-tre-em',
                'description' => 'Gọng kính dành cho trẻ em với thiết kế an toàn và màu sắc bắt mắt',
                'parent_id' => null,
                'sort_order' => 5,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }

        Category::whereIn('slug', [
            'kinh-can',
            'kinh-vien',
            'kinh-da-tri',
            'gong-kinh',
            'gong-kim-loai',
            'gong-nhua',
        ])->update(['is_active' => false]);

        $this->command->info('Seeded ' . count($categories) . ' categories.');
    }
}
