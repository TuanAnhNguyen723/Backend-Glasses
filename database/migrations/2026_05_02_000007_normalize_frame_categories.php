<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('categories')) {
            return;
        }

        $now = now();
        $frameCategories = [
            [
                'old_slug' => null,
                'slug' => 'gong-nam',
                'name' => 'Gọng Nam',
                'description' => 'Gọng kính phù hợp phong cách nam',
                'sort_order' => 1,
            ],
            [
                'old_slug' => null,
                'slug' => 'gong-nu',
                'name' => 'Gọng Nữ',
                'description' => 'Gọng kính phù hợp phong cách nữ',
                'sort_order' => 2,
            ],
            [
                'old_slug' => null,
                'slug' => 'gong-unisex',
                'name' => 'Gọng Unisex',
                'description' => 'Gọng kính trung tính, phù hợp cho cả nam và nữ',
                'sort_order' => 3,
            ],
            [
                'old_slug' => 'kinh-mat',
                'slug' => 'kinh-mat',
                'name' => 'Kính Mát',
                'description' => 'Kính mát thời trang với khả năng chống tia UV',
                'sort_order' => 4,
            ],
            [
                'old_slug' => 'kinh-tre-em',
                'slug' => 'gong-tre-em',
                'name' => 'Gọng Trẻ Em',
                'description' => 'Gọng kính dành cho trẻ em với thiết kế an toàn và màu sắc bắt mắt',
                'sort_order' => 5,
            ],
        ];

        foreach ($frameCategories as $category) {
            $old = $category['old_slug']
                ? DB::table('categories')->where('slug', $category['old_slug'])->first()
                : null;
            $target = DB::table('categories')->where('slug', $category['slug'])->first();

            if ($old && ! $target) {
                DB::table('categories')->where('id', $old->id)->update([
                    'name' => $category['name'],
                    'slug' => $category['slug'],
                    'description' => $category['description'],
                    'sort_order' => $category['sort_order'],
                    'is_active' => true,
                    'updated_at' => $now,
                ]);
                continue;
            }

            if (! $target) {
                $targetId = DB::table('categories')->insertGetId([
                    'name' => $category['name'],
                    'slug' => $category['slug'],
                    'description' => $category['description'],
                    'parent_id' => null,
                    'sort_order' => $category['sort_order'],
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            } else {
                $targetId = $target->id;
                DB::table('categories')->where('id', $targetId)->update([
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'sort_order' => $category['sort_order'],
                    'is_active' => true,
                    'updated_at' => $now,
                ]);
            }

            if ($old && $old->id !== $targetId) {
                if (Schema::hasTable('products')) {
                    DB::table('products')
                        ->where('category_id', $old->id)
                        ->update(['category_id' => $targetId, 'updated_at' => $now]);
                }

                DB::table('categories')->where('id', $old->id)->update([
                    'name' => $old->name . ' (cũ - không dùng)',
                    'is_active' => false,
                    'updated_at' => $now,
                ]);
            }
        }

        if (Schema::hasTable('products')) {
            $categoryIds = DB::table('categories')
                ->whereIn('slug', ['gong-nam', 'gong-nu', 'gong-unisex', 'kinh-mat'])
                ->pluck('id', 'slug');

            foreach ($this->frameProductRenames() as $sku => $data) {
                $categorySlug = $data['category_slug'] ?? null;
                unset($data['category_slug']);
                if ($categorySlug && isset($categoryIds[$categorySlug])) {
                    $data['category_id'] = $categoryIds[$categorySlug];
                }

                DB::table('products')
                    ->where('sku', $sku)
                    ->update(array_merge($data, ['updated_at' => $now]));
            }

            if (isset($categoryIds['gong-unisex'])) {
                $legacyCategoryIds = DB::table('categories')
                    ->whereIn('slug', ['kinh-can', 'kinh-vien', 'kinh-da-tri', 'gong-kinh', 'gong-kim-loai', 'gong-nhua'])
                    ->pluck('id');

                if ($legacyCategoryIds->isNotEmpty()) {
                    DB::table('products')
                        ->whereIn('category_id', $legacyCategoryIds)
                        ->update([
                            'category_id' => $categoryIds['gong-unisex'],
                            'updated_at' => $now,
                        ]);
                }
            }
        }

        DB::table('categories')
            ->whereIn('slug', ['kinh-can', 'kinh-vien', 'kinh-da-tri', 'gong-kinh', 'gong-kim-loai', 'gong-nhua'])
            ->update([
                'is_active' => false,
                'updated_at' => $now,
            ]);
    }

    public function down(): void
    {
        // Không rollback dữ liệu danh mục để tránh chuyển nhầm sản phẩm đã được gán mới.
    }

    private function frameProductRenames(): array
    {
        return [
            'KC-001' => [
                'category_slug' => 'gong-unisex',
                'name' => 'Gọng Ray-Ban Aviator Classic',
                'slug' => 'gong-ray-ban-aviator-classic',
                'short_description' => 'Gọng Ray-Ban Aviator Classic - Thiết kế cổ điển, gọng kim loại',
                'meta_title' => 'Gọng Ray-Ban Aviator Classic',
                'meta_description' => 'Gọng Ray-Ban Aviator Classic chất lượng cao, thiết kế cổ điển',
            ],
            'KC-002' => [
                'category_slug' => 'gong-nam',
                'name' => 'Gọng Oakley Wayfarer Premium',
                'slug' => 'gong-oakley-wayfarer-premium',
                'short_description' => 'Gọng Oakley Wayfarer Premium - Gọng nhựa cao cấp, thiết kế hiện đại',
                'meta_title' => 'Gọng Oakley Wayfarer Premium',
                'meta_description' => 'Gọng Oakley Wayfarer Premium chất lượng cao',
            ],
            'KC-003' => [
                'category_slug' => 'gong-nu',
                'name' => 'Gọng Gucci Round Vintage',
                'slug' => 'gong-gucci-round-vintage',
                'short_description' => 'Gọng Gucci Round Vintage - Thiết kế tròn cổ điển, gọng mạ vàng',
                'meta_title' => 'Gọng Gucci Round Vintage',
                'meta_description' => 'Gọng Gucci Round Vintage cao cấp, thiết kế sang trọng',
            ],
            'KC-004' => [
                'category_slug' => 'gong-nam',
                'name' => 'Gọng Prada Square Modern',
                'slug' => 'gong-prada-square-modern',
                'short_description' => 'Gọng Prada Square Modern - Thiết kế vuông hiện đại, gọng nhựa đen',
                'meta_title' => 'Gọng Prada Square Modern',
                'meta_description' => 'Gọng Prada Square Modern thiết kế hiện đại',
            ],
            'KC-005' => [
                'category_slug' => 'gong-nu',
                'name' => 'Gọng Tom Ford Cat-Eye Elegant',
                'slug' => 'gong-tom-ford-cat-eye-elegant',
                'short_description' => 'Gọng Tom Ford Cat-Eye Elegant - Thiết kế mắt mèo thanh lịch',
                'meta_title' => 'Gọng Tom Ford Cat-Eye Elegant',
                'meta_description' => 'Gọng Tom Ford Cat-Eye Elegant thiết kế thanh lịch cho phụ nữ',
            ],
            'KV-001' => [
                'category_slug' => 'gong-unisex',
                'name' => 'Gọng Ray-Ban Classic',
                'slug' => 'gong-ray-ban-classic',
                'short_description' => 'Gọng Ray-Ban Classic - Thiết kế cổ điển cho người lớn tuổi',
                'meta_title' => 'Gọng Ray-Ban Classic',
                'meta_description' => 'Gọng Ray-Ban Classic chất lượng cao cho người lớn tuổi',
            ],
        ];
    }
};
