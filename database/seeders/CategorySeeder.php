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
                'name' => 'Kính Cận',
                'slug' => 'kinh-can',
                'description' => 'Kính cận chất lượng cao với nhiều loại gọng đa dạng',
                'parent_id' => null,
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Kính Viễn',
                'slug' => 'kinh-vien',
                'description' => 'Kính viễn cho người lớn tuổi, thiết kế sang trọng',
                'parent_id' => null,
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Kính Đa Trị',
                'slug' => 'kinh-da-tri',
                'description' => 'Kính đa trị tích hợp cận và viễn trong một tròng kính',
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
                'name' => 'Kính Trẻ Em',
                'slug' => 'kinh-tre-em',
                'description' => 'Kính dành cho trẻ em với thiết kế an toàn và màu sắc bắt mắt',
                'parent_id' => null,
                'sort_order' => 5,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        $this->command->info('Created ' . count($categories) . ' categories.');
    }
}
