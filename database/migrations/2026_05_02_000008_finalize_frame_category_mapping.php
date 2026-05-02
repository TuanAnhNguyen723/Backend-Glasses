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
        $categories = [
            'gong-nam' => [
                'name' => 'Gọng Nam',
                'description' => 'Gọng kính phù hợp phong cách nam',
                'sort_order' => 1,
            ],
            'gong-nu' => [
                'name' => 'Gọng Nữ',
                'description' => 'Gọng kính phù hợp phong cách nữ',
                'sort_order' => 2,
            ],
            'gong-unisex' => [
                'name' => 'Gọng Unisex',
                'description' => 'Gọng kính trung tính, phù hợp cho cả nam và nữ',
                'sort_order' => 3,
            ],
            'kinh-mat' => [
                'name' => 'Kính Mát',
                'description' => 'Kính mát thời trang với khả năng chống tia UV',
                'sort_order' => 4,
            ],
            'gong-tre-em' => [
                'name' => 'Gọng Trẻ Em',
                'description' => 'Gọng kính dành cho trẻ em với thiết kế an toàn và màu sắc bắt mắt',
                'sort_order' => 5,
            ],
        ];

        foreach ($categories as $slug => $data) {
            $existing = DB::table('categories')->where('slug', $slug)->first();
            if ($existing) {
                DB::table('categories')->where('id', $existing->id)->update(array_merge($data, [
                    'is_active' => true,
                    'updated_at' => $now,
                ]));
                continue;
            }

            DB::table('categories')->insert(array_merge($data, [
                'slug' => $slug,
                'parent_id' => null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        $activeIds = DB::table('categories')
            ->whereIn('slug', array_keys($categories))
            ->pluck('id', 'slug');

        if (Schema::hasTable('products') && isset($activeIds['gong-unisex'])) {
            $legacyCategoryIds = DB::table('categories')
                ->whereIn('slug', ['kinh-can', 'kinh-vien', 'kinh-da-tri', 'gong-kinh', 'gong-kim-loai', 'gong-nhua'])
                ->pluck('id');

            if ($legacyCategoryIds->isNotEmpty()) {
                DB::table('products')
                    ->whereIn('category_id', $legacyCategoryIds)
                    ->update([
                        'category_id' => $activeIds['gong-unisex'],
                        'updated_at' => $now,
                    ]);
            }

            foreach ($this->sampleProductCategoryMap() as $sku => $categorySlug) {
                if (! isset($activeIds[$categorySlug])) {
                    continue;
                }

                DB::table('products')
                    ->where('sku', $sku)
                    ->update([
                        'category_id' => $activeIds[$categorySlug],
                        'updated_at' => $now,
                    ]);
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
        // Không rollback data normalization để tránh đưa product về category lens cũ.
    }

    private function sampleProductCategoryMap(): array
    {
        return [
            'KC-001' => 'gong-unisex',
            'KC-002' => 'gong-nam',
            'KC-003' => 'gong-nu',
            'KC-004' => 'gong-nam',
            'KC-005' => 'gong-nu',
            'KV-001' => 'gong-unisex',
            'KM-001' => 'kinh-mat',
        ];
    }
};
