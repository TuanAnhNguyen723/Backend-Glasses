<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;
use App\Models\ProductColor;
use App\Models\LensOption;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all();
        
        if ($categories->isEmpty()) {
            $this->command->warn('No categories found. Please run CategorySeeder first.');
            return;
        }

        $products = [
            [
                'sku' => 'KC-001',
                'name' => 'Kính Cận Ray-Ban Aviator Classic',
                'slug' => 'kinh-can-ray-ban-aviator-classic',
                'description' => 'Kính cận Ray-Ban Aviator Classic với thiết kế cổ điển, gọng kim loại mỏng nhẹ. Phù hợp cho cả nam và nữ, mang lại phong cách thời trang và sang trọng.',
                'short_description' => 'Kính cận Ray-Ban Aviator Classic - Thiết kế cổ điển, gọng kim loại',
                'base_price' => 2500000,
                'compare_price' => 3000000,
                'category_id' => $categories->where('slug', 'kinh-can')->first()->id,
                'frame_shape' => 'aviator',
                'material' => 'Kim loại',
                'size' => '58-14-140',
                'bridge' => '14mm',
                'stock_quantity' => 50,
                'low_stock_threshold' => 10,
                'rating_average' => 4.5,
                'rating_count' => 120,
                'review_count' => 85,
                'badge' => 'Bestseller',
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 1,
                'meta_title' => 'Kính Cận Ray-Ban Aviator Classic',
                'meta_description' => 'Kính cận Ray-Ban Aviator Classic chất lượng cao, thiết kế cổ điển',
            ],
            [
                'sku' => 'KC-002',
                'name' => 'Kính Cận Oakley Wayfarer Premium',
                'slug' => 'kinh-can-oakley-wayfarer-premium',
                'description' => 'Kính cận Oakley Wayfarer Premium với gọng nhựa cao cấp, thiết kế hiện đại. Chống trầy xước và chống tia UV, phù hợp cho mọi hoạt động hàng ngày.',
                'short_description' => 'Kính cận Oakley Wayfarer Premium - Gọng nhựa cao cấp, thiết kế hiện đại',
                'base_price' => 3200000,
                'compare_price' => 3800000,
                'category_id' => $categories->where('slug', 'kinh-can')->first()->id,
                'frame_shape' => 'wayfarer',
                'material' => 'Nhựa Acetate',
                'size' => '54-18-145',
                'bridge' => '18mm',
                'stock_quantity' => 35,
                'low_stock_threshold' => 10,
                'rating_average' => 4.7,
                'rating_count' => 95,
                'review_count' => 68,
                'badge' => 'New',
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 2,
                'meta_title' => 'Kính Cận Oakley Wayfarer Premium',
                'meta_description' => 'Kính cận Oakley Wayfarer Premium chất lượng cao, chống trầy xước',
            ],
            [
                'sku' => 'KC-003',
                'name' => 'Kính Cận Gucci Round Vintage',
                'slug' => 'kinh-can-gucci-round-vintage',
                'description' => 'Kính cận Gucci Round Vintage với thiết kế tròn cổ điển, gọng kim loại mạ vàng sang trọng. Logo Gucci nổi bật trên gọng, thể hiện đẳng cấp thời trang.',
                'short_description' => 'Kính cận Gucci Round Vintage - Thiết kế tròn cổ điển, gọng mạ vàng',
                'base_price' => 4500000,
                'compare_price' => 5500000,
                'category_id' => $categories->where('slug', 'kinh-can')->first()->id,
                'frame_shape' => 'round',
                'material' => 'Kim loại mạ vàng',
                'size' => '52-16-135',
                'bridge' => '16mm',
                'stock_quantity' => 25,
                'low_stock_threshold' => 5,
                'rating_average' => 4.8,
                'rating_count' => 75,
                'review_count' => 52,
                'badge' => 'Premium',
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 3,
                'meta_title' => 'Kính Cận Gucci Round Vintage',
                'meta_description' => 'Kính cận Gucci Round Vintage cao cấp, thiết kế sang trọng',
            ],
            [
                'sku' => 'KC-004',
                'name' => 'Kính Cận Prada Square Modern',
                'slug' => 'kinh-can-prada-square-modern',
                'description' => 'Kính cận Prada Square Modern với thiết kế vuông hiện đại, gọng nhựa màu đen bóng. Phù hợp cho phong cách công sở và thời trang đường phố.',
                'short_description' => 'Kính cận Prada Square Modern - Thiết kế vuông hiện đại, gọng nhựa đen',
                'base_price' => 3800000,
                'compare_price' => 4500000,
                'category_id' => $categories->where('slug', 'kinh-can')->first()->id,
                'frame_shape' => 'square',
                'material' => 'Nhựa Acetate',
                'size' => '56-20-150',
                'bridge' => '20mm',
                'stock_quantity' => 40,
                'low_stock_threshold' => 10,
                'rating_average' => 4.6,
                'rating_count' => 88,
                'review_count' => 61,
                'badge' => null,
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 4,
                'meta_title' => 'Kính Cận Prada Square Modern',
                'meta_description' => 'Kính cận Prada Square Modern thiết kế hiện đại',
            ],
            [
                'sku' => 'KC-005',
                'name' => 'Kính Cận Tom Ford Cat-Eye Elegant',
                'slug' => 'kinh-can-tom-ford-cat-eye-elegant',
                'description' => 'Kính cận Tom Ford Cat-Eye Elegant với thiết kế mắt mèo thanh lịch, gọng kim loại mỏng. Phù hợp cho phụ nữ, mang lại vẻ đẹp quyến rũ và sang trọng.',
                'short_description' => 'Kính cận Tom Ford Cat-Eye Elegant - Thiết kế mắt mèo thanh lịch',
                'base_price' => 4200000,
                'compare_price' => 5000000,
                'category_id' => $categories->where('slug', 'kinh-can')->first()->id,
                'frame_shape' => 'cat-eye',
                'material' => 'Kim loại',
                'size' => '50-18-140',
                'bridge' => '18mm',
                'stock_quantity' => 30,
                'low_stock_threshold' => 8,
                'rating_average' => 4.9,
                'rating_count' => 65,
                'review_count' => 48,
                'badge' => 'Hot',
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 5,
                'meta_title' => 'Kính Cận Tom Ford Cat-Eye Elegant',
                'meta_description' => 'Kính cận Tom Ford Cat-Eye Elegant thiết kế thanh lịch cho phụ nữ',
            ],
            [
                'sku' => 'KV-001',
                'name' => 'Kính Viễn Ray-Ban Classic',
                'slug' => 'kinh-vien-ray-ban-classic',
                'description' => 'Kính viễn Ray-Ban Classic với thiết kế cổ điển, gọng kim loại chắc chắn. Phù hợp cho người lớn tuổi, mang lại tầm nhìn rõ ràng và thoải mái.',
                'short_description' => 'Kính viễn Ray-Ban Classic - Thiết kế cổ điển cho người lớn tuổi',
                'base_price' => 2800000,
                'compare_price' => 3300000,
                'category_id' => $categories->where('slug', 'kinh-vien')->first()->id,
                'frame_shape' => 'rectangular',
                'material' => 'Kim loại',
                'size' => '54-16-145',
                'bridge' => '16mm',
                'stock_quantity' => 45,
                'low_stock_threshold' => 10,
                'rating_average' => 4.4,
                'rating_count' => 110,
                'review_count' => 78,
                'badge' => 'Bestseller',
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 1,
                'meta_title' => 'Kính Viễn Ray-Ban Classic',
                'meta_description' => 'Kính viễn Ray-Ban Classic chất lượng cao cho người lớn tuổi',
            ],
            [
                'sku' => 'KM-001',
                'name' => 'Kính Mát Ray-Ban Aviator Sunglasses',
                'slug' => 'kinh-mat-ray-ban-aviator-sunglasses',
                'description' => 'Kính mát Ray-Ban Aviator với tròng kính chống tia UV 100%, thiết kế cổ điển không bao giờ lỗi thời. Phù hợp cho cả nam và nữ.',
                'short_description' => 'Kính mát Ray-Ban Aviator - Chống tia UV 100%, thiết kế cổ điển',
                'base_price' => 2200000,
                'compare_price' => 2800000,
                'category_id' => $categories->where('slug', 'kinh-mat')->first()->id,
                'frame_shape' => 'aviator',
                'material' => 'Kim loại',
                'size' => '58-14-140',
                'bridge' => '14mm',
                'stock_quantity' => 60,
                'low_stock_threshold' => 15,
                'rating_average' => 4.6,
                'rating_count' => 150,
                'review_count' => 105,
                'badge' => 'Bestseller',
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 1,
                'meta_title' => 'Kính Mát Ray-Ban Aviator Sunglasses',
                'meta_description' => 'Kính mát Ray-Ban Aviator chống tia UV 100%',
            ],
        ];

        foreach ($products as $productData) {
            $product = Product::create($productData);

            // Tạo Product Images
            $images = [
                [
                    'image_url' => 'https://images.unsplash.com/photo-1572635196237-14b3f281503f?w=500',
                    'alt_text' => $product->name . ' - Hình ảnh chính',
                    'sort_order' => 1,
                    'is_primary' => true,
                ],
                [
                    'image_url' => 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?w=500',
                    'alt_text' => $product->name . ' - Hình ảnh phụ 1',
                    'sort_order' => 2,
                    'is_primary' => false,
                ],
                [
                    'image_url' => 'https://images.unsplash.com/photo-1574258495973-f010dfbb5371?w=500',
                    'alt_text' => $product->name . ' - Hình ảnh phụ 2',
                    'sort_order' => 3,
                    'is_primary' => false,
                ],
            ];

            foreach ($images as $image) {
                ProductImage::create(array_merge($image, ['product_id' => $product->id]));
            }

            // Tạo Product Colors
            $colors = [
                [
                    'name' => 'Đen',
                    'hex_code' => '#000000',
                    'price_adjustment' => 0,
                    'stock_quantity' => 20,
                    'is_active' => true,
                    'sort_order' => 1,
                ],
                [
                    'name' => 'Nâu',
                    'hex_code' => '#8B4513',
                    'price_adjustment' => 0,
                    'stock_quantity' => 15,
                    'is_active' => true,
                    'sort_order' => 2,
                ],
                [
                    'name' => 'Xám',
                    'hex_code' => '#808080',
                    'price_adjustment' => 0,
                    'stock_quantity' => 18,
                    'is_active' => true,
                    'sort_order' => 3,
                ],
            ];

            foreach ($colors as $color) {
                ProductColor::create(array_merge($color, ['product_id' => $product->id]));
            }

            // Tạo Lens Options
            $lensOptions = [
                [
                    'name' => 'Tròng kính cơ bản',
                    'description' => 'Tròng kính cơ bản chống trầy xước',
                    'price_adjustment' => 0,
                    'is_default' => true,
                    'is_active' => true,
                    'sort_order' => 1,
                ],
                [
                    'name' => 'Tròng kính chống ánh sáng xanh',
                    'description' => 'Bảo vệ mắt khỏi ánh sáng xanh từ màn hình',
                    'price_adjustment' => 500000,
                    'is_default' => false,
                    'is_active' => true,
                    'sort_order' => 2,
                ],
                [
                    'name' => 'Tròng kính đổi màu',
                    'description' => 'Tự động đổi màu khi ra nắng',
                    'price_adjustment' => 800000,
                    'is_default' => false,
                    'is_active' => true,
                    'sort_order' => 3,
                ],
                [
                    'name' => 'Tròng kính chống phản quang',
                    'description' => 'Giảm phản quang, tăng độ rõ nét',
                    'price_adjustment' => 300000,
                    'is_default' => false,
                    'is_active' => true,
                    'sort_order' => 4,
                ],
            ];

            foreach ($lensOptions as $lens) {
                LensOption::create(array_merge($lens, ['product_id' => $product->id]));
            }
        }

        $this->command->info('Created ' . count($products) . ' products with images, colors, and lens options.');
    }
}
