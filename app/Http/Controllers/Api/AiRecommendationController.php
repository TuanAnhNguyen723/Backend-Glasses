<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Ai\ProductRecommendationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AiRecommendationController extends Controller
{
    public function __construct(private ProductRecommendationService $recommendationService)
    {
    }

    public function recommend(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $message = trim($validated['message']);
        $cacheKey = 'ai_recommend_products:'.sha1(mb_strtolower($message));
        $ttlSeconds = 90;

        $payload = Cache::remember($cacheKey, $ttlSeconds, function () use ($message) {
            $filters = $this->recommendationService->parseFilters($message);
            $products = $this->recommendationService->getProducts($filters, 5);
            $reply = $this->recommendationService->buildFriendlyReply($message, $filters, $products);

            return [
                'reply' => $reply,
                'filters' => [
                    'frame_shape' => $filters['frame_shapes'],
                    'min_price' => $filters['min_price'],
                    'max_price' => $filters['max_price'],
                    'style' => $filters['usage'],
                ],
                'products' => $products->map(function (Product $product) use ($filters) {
                    $reason = $this->buildProductReason($product, $filters);

                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'price' => (int) $product->base_price,
                        'image' => optional($product->primaryImage)->image_url,
                        'reason' => $reason,
                    ];
                })->values(),
            ];
        });

        return response()->json($payload);
    }

    private function buildProductReason(Product $product, array $filters): string
    {
        $parts = [];

        if (! empty($filters['frame_shapes']) && in_array($product->frame_shape, $filters['frame_shapes'], true)) {
            $parts[] = 'đúng kiểu gọng bạn đang ưu tiên';
        }
        if ($filters['max_price'] !== null && (int) $product->base_price <= (int) $filters['max_price']) {
            $parts[] = 'nằm trong ngân sách';
        }
        if ($filters['usage'] === 'daily') {
            $parts[] = 'dễ phối đồ để đeo hằng ngày';
        } elseif ($filters['usage'] === 'office') {
            $parts[] = 'phù hợp môi trường công sở';
        }

        if (empty($parts)) {
            return 'Mẫu này cân bằng giữa kiểu dáng và mức giá.';
        }

        return 'Mẫu này '.implode(', ', $parts).'.';
    }
}
