<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            [
                'name' => 'Ray-Ban',
                'slug' => Str::slug('Ray-Ban'),
                'description' => 'Established 1937',
                'logo_url' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDW95oM1_oc3OYyrkQBv3JrWbuA3C6o7uKWYsuj2GIgQLdvWZvYaOAU9e-DUsqyV2c8xXNqBwb7X-T2bdcXu8qmuth5xhE_xx5gFZ3t6G861tDJHTiNg4dRRmuN5AZbSE6IWQAt5hhgN3F_Hgcj4acXtpZW0MAQ5-jFu9sYYOMzYwHd8SjfoGJ_xkfTUjJw86-oOm7ZADr5ZlQKAafBLmpnzrS_1yCQQ8DnYjjUBWVhEqQyCKQ1w4wQZgjECCvR93ZU2AZi1ldqfA',
                'website_url' => 'https://www.ray-ban.com',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Oakley',
                'slug' => Str::slug('Oakley'),
                'description' => 'Sports & Performance',
                'logo_url' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuCI0TYzjdxGaiEMPm8OY89qzF6IQu-UovvNdmW-_aYCIFJN1P8uIZziAI7HxlufdRGqRkY4tI72Xxq_9E_hq3ci-VrliOue1-jKbvYuQG5H42-qmw3yF3pCsoetrMXnYMWfCIfBC5Ey3402DBbRYTIPzsLKAwvl-uO0WI15D9KKdLharqxdVf5VLjLbNTNtf3R8DpSICt35TnqIxHuCZMfuHMrYE6PIcDMjVtwSNms8x_faByJv_gk6xtIgrcfqFegdwUjiumw_5A',
                'website_url' => 'https://www.oakley.com',
                'is_active' => true,
                'sort_order' => 2,
            ],
        ];

        foreach ($brands as $brandData) {
            Brand::updateOrCreate(
                ['slug' => $brandData['slug']],
                $brandData
            );
        }
    }
}
