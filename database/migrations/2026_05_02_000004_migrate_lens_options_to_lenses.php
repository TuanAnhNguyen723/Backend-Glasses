<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (!DB::getSchemaBuilder()->hasTable('lens_options') || !DB::getSchemaBuilder()->hasTable('lenses')) {
            return;
        }

        $options = DB::table('lens_options')
            ->select('id', 'name', 'description', 'price_adjustment', 'is_active', 'sort_order')
            ->orderBy('id')
            ->get();

        $mapping = [];
        $existingCount = (int) DB::table('lenses')->count();
        $seed = $existingCount + 1;
        $hasRequiresPrescription = DB::getSchemaBuilder()->hasColumn('lenses', 'requires_prescription');

        foreach ($options as $opt) {
            $name = trim((string) $opt->name);
            if ($name === '') {
                continue;
            }

            $slugBase = Str::slug($name);
            if ($slugBase === '') {
                $slugBase = 'lens';
            }

            $slug = $slugBase;
            $suffix = 1;
            while (DB::table('lenses')->where('slug', $slug)->exists()) {
                $suffix++;
                $slug = $slugBase . '-' . $suffix;
            }

            $sku = 'LENS-' . str_pad((string) $seed, 5, '0', STR_PAD_LEFT);
            while (DB::table('lenses')->where('sku', $sku)->exists()) {
                $seed++;
                $sku = 'LENS-' . str_pad((string) $seed, 5, '0', STR_PAD_LEFT);
            }

            $existingLens = DB::table('lenses')
                ->where('name', $name)
                ->where('base_price', (float) $opt->price_adjustment)
                ->first();

            if ($existingLens) {
                $mapping[(int) $opt->id] = (int) $existingLens->id;
                continue;
            }

            $row = [
                'sku' => $sku,
                'name' => $name,
                'slug' => $slug,
                'description' => $opt->description,
                'lens_type' => 'myopia',
                'base_price' => (float) $opt->price_adjustment,
                'stock_quantity' => 0,
                'is_active' => (bool) $opt->is_active,
                'sort_order' => (int) $opt->sort_order,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if ($hasRequiresPrescription) {
                $row['requires_prescription'] = true;
            }

            $lensId = DB::table('lenses')->insertGetId($row);

            $mapping[(int) $opt->id] = $lensId;
            $seed++;
        }

        if (DB::getSchemaBuilder()->hasTable('cart_items') && DB::getSchemaBuilder()->hasColumn('cart_items', 'lens_option_id') && DB::getSchemaBuilder()->hasColumn('cart_items', 'lens_id')) {
            DB::table('cart_items')
                ->whereNotNull('lens_option_id')
                ->orderBy('id')
                ->get(['id', 'lens_option_id'])
                ->each(function ($item) use ($mapping) {
                    $lensId = $mapping[(int) $item->lens_option_id] ?? null;
                    if ($lensId) {
                        DB::table('cart_items')->where('id', $item->id)->update(['lens_id' => $lensId]);
                    }
                });
        }

        if (DB::getSchemaBuilder()->hasTable('order_items')) {
            DB::table('order_items')
                ->whereNull('lens_name')
                ->whereNotNull('lens_option_name')
                ->update(['lens_name' => DB::raw('lens_option_name')]);
        }
    }

    public function down(): void
    {
        // Giữ dữ liệu lịch sử, không rollback mapping ngược.
    }
};
