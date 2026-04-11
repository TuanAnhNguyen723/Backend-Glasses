<?php

namespace App\Services\Ai;

use App\Models\Product;
use Illuminate\Support\Collection;

class ProductRecommendationService
{
    private const ALLOWED_SHAPES = ['aviator', 'wayfarer', 'round', 'square', 'cat_eye'];
    private const ALLOWED_USAGE = ['daily', 'office', 'sport', 'fashion'];
    private const ALLOWED_COLOR_TONE = ['neutral', 'bold', 'any'];

    public function __construct(private GeminiClient $geminiClient)
    {
    }

    public function parseFilters(string $message): array
    {
        $prompt = <<<PROMPT
Bạn là bộ parser intent cho hệ thống gợi ý kính mắt.
NHIỆM VỤ: Trả về DUY NHẤT JSON hợp lệ, KHÔNG markdown, KHÔNG giải thích.

Schema bắt buộc:
{
  "frame_shapes": ["aviator|wayfarer|round|square|cat_eye"],
  "min_price": number|null,
  "max_price": number|null,
  "usage": "daily|office|sport|fashion",
  "color_tone": "neutral|bold|any"
}

Quy tắc:
- frame_shapes là mảng, loại bỏ giá trị ngoài danh sách.
- Nếu không rõ min_price/max_price thì trả null.
- Nếu không rõ usage thì mặc định "daily".
- Nếu không rõ color_tone thì mặc định "any".

User message:
{$message}
PROMPT;

        $raw = $this->geminiClient->generateText($prompt);
        $decoded = $raw ? $this->geminiClient->extractJsonObject($raw) : null;

        return $this->normalizeFilters($decoded, $message);
    }

    public function getProducts(array $filters, int $limit = 5): Collection
    {
        $query = Product::query()
            ->with('primaryImage')
            ->active()
            ->where('stock_quantity', '>', 0);

        if (! empty($filters['frame_shapes'])) {
            $query->whereIn('frame_shape', $filters['frame_shapes']);
        }

        if ($filters['min_price'] !== null) {
            $query->where('base_price', '>=', $filters['min_price']);
        }

        if ($filters['max_price'] !== null) {
            $query->where('base_price', '<=', $filters['max_price']);
        }

        return $query
            ->orderByDesc('is_featured')
            ->orderByDesc('rating_average')
            ->orderByDesc('review_count')
            ->limit($limit)
            ->get();
    }

    public function buildFriendlyReply(string $message, array $filters, Collection $products): string
    {
        $productsForPrompt = $products->map(fn (Product $product) => [
            'name' => $product->name,
            'price' => (float) $product->base_price,
            'frame_shape' => $product->frame_shape,
        ])->values()->all();

        $prompt = <<<PROMPT
Bạn là stylist kính mắt. Viết 1-2 câu tiếng Việt, thân thiện, ngắn gọn.
Yêu cầu:
- Tóm tắt vì sao hợp với nhu cầu người dùng.
- Nhắc nhanh về kiểu gọng và ngân sách.
- Không dùng markdown.

User message:
{$message}

Filters:
{$this->toJson($filters)}

Products:
{$this->toJson($productsForPrompt)}
PROMPT;

        $reply = $this->geminiClient->generateText($prompt);
        if (is_string($reply) && trim($reply) !== '') {
            return trim($reply);
        }

        $shapeText = empty($filters['frame_shapes']) ? 'kiểu linh hoạt' : implode('/', $filters['frame_shapes']);
        $budgetText = $filters['max_price'] ? 'ngân sách khoảng '.number_format((int) $filters['max_price'], 0, ',', '.').'đ' : 'ngân sách linh hoạt';

        return "Mình gợi ý bạn ưu tiên gọng {$shapeText} để đeo cân mặt hơn, đồng thời chọn mẫu trong {$budgetText} để dễ dùng hằng ngày.";
    }

    private function normalizeFilters(?array $decoded, string $message): array
    {
        $frameShapes = array_values(array_filter(
            (array) data_get($decoded, 'frame_shapes', []),
            fn ($shape) => is_string($shape) && in_array($shape, self::ALLOWED_SHAPES, true)
        ));

        $minPrice = $this->toNumberOrNull(data_get($decoded, 'min_price'));
        $maxPrice = $this->toNumberOrNull(data_get($decoded, 'max_price'));
        $usage = data_get($decoded, 'usage', 'daily');
        $colorTone = data_get($decoded, 'color_tone', 'any');

        if (! is_string($usage) || ! in_array($usage, self::ALLOWED_USAGE, true)) {
            $usage = 'daily';
        }
        if (! is_string($colorTone) || ! in_array($colorTone, self::ALLOWED_COLOR_TONE, true)) {
            $colorTone = 'any';
        }
        if ($minPrice !== null && $maxPrice !== null && $minPrice > $maxPrice) {
            [$minPrice, $maxPrice] = [$maxPrice, $minPrice];
        }

        // Fallback heuristic: nếu AI không parse được ngân sách thì lấy nhanh từ message.
        if ($maxPrice === null) {
            $maxPrice = $this->extractBudgetFromMessage($message);
        }

        return [
            'frame_shapes' => $frameShapes,
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'usage' => $usage,
            'color_tone' => $colorTone,
        ];
    }

    private function extractBudgetFromMessage(string $message): ?int
    {
        $normalized = mb_strtolower($message);
        if (preg_match('/(\d+)\s*(tr|triệu)/u', $normalized, $m) === 1) {
            return ((int) $m[1]) * 1000000;
        }

        if (preg_match('/(\d{5,9})/u', $normalized, $m) === 1) {
            return (int) $m[1];
        }

        return null;
    }

    private function toNumberOrNull(mixed $value): ?int
    {
        if (is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }

    private function toJson(array $payload): string
    {
        return (string) json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
