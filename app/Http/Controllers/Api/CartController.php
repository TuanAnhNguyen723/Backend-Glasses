<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartItemResource;
use App\Models\CartItem;
use App\Models\Lens;
use App\Models\Product;
use App\Models\ProductColor;
use App\Models\LensOption;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Lấy giỏ hàng của user (items + tổng tiền).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $items = CartItem::query()
            ->where('user_id', $user->id)
            ->with(['product.primaryImage', 'productColor', 'lens', 'lensOption'])
            ->orderBy('created_at', 'desc')
            ->get();

        $subtotal = $items->sum(fn (CartItem $item) => (float) $item->unit_price * $item->quantity);

        return response()->json([
            'data' => [
                'items' => CartItemResource::collection($items),
                'subtotal' => round($subtotal, 2),
                'item_count' => $items->sum('quantity'),
            ],
        ]);
    }

    /**
     * Thêm sản phẩm vào giỏ hàng.
     * Body: product_id, quantity (optional), product_color_id (optional), lens_id (optional), prescription (optional)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'nullable|integer|min:1|max:99',
            'product_color_id' => 'nullable|exists:product_colors,id',
            'lens_id' => 'nullable|exists:lenses,id',
            'lens_option_id' => 'nullable|exists:lens_options,id',
            'prescription_type' => 'nullable|string|in:myopia,hyperopia,reading,other',
            'prescription' => 'nullable|array',
            'prescription.right_sphere' => 'nullable|numeric|between:-30,30',
            'prescription.right_cylinder' => 'nullable|numeric|between:-10,10',
            'prescription.right_axis' => 'nullable|integer|between:0,180',
            'prescription.left_sphere' => 'nullable|numeric|between:-30,30',
            'prescription.left_cylinder' => 'nullable|numeric|between:-10,10',
            'prescription.left_axis' => 'nullable|integer|between:0,180',
            'prescription.pd' => 'nullable|numeric|between:40,80',
            'prescription.notes' => 'nullable|string|max:1000',
            'prescription.image_url' => 'nullable|url|max:500',
        ]);

        $quantity = (int) ($validated['quantity'] ?? 1);
        $product = Product::findOrFail($validated['product_id']);

        // Kiểm tra product_color và lens_option thuộc đúng product
        $productColorId = $validated['product_color_id'] ?? null;
        $lensId = $validated['lens_id'] ?? null;
        $lensOptionId = $validated['lens_option_id'] ?? null;
        if ($lensId) {
            $lensOptionId = null;
        }

        if ($productColorId) {
            ProductColor::where('id', $productColorId)->where('product_id', $product->id)->firstOrFail();
        }
        $lens = null;
        if ($lensId) {
            $lens = Lens::where('id', $lensId)->firstOrFail();
        } elseif ($lensOptionId) {
            LensOption::where('id', $lensOptionId)->where('product_id', $product->id)->firstOrFail();
        }

        $prescriptionData = $this->normalizePrescriptionData(
            $validated['prescription'] ?? [],
            $validated['prescription_type'] ?? null
        );
        $this->ensurePrescriptionIsPresentIfRequired($lens, $prescriptionData);
        $prescriptionHash = $this->makePrescriptionHash($prescriptionData);

        $unitPrice = $this->calculateUnitPrice($product, $productColorId, $lensId, $lensOptionId);

        $existing = CartItem::query()
            ->where('user_id', $request->user()->id)
            ->where('product_id', $product->id)
            ->where('product_color_id', $productColorId)
            ->where('lens_id', $lensId)
            ->where('lens_option_id', $lensOptionId)
            ->where('prescription_hash', $prescriptionHash)
            ->first();

        if ($existing) {
            $existing->quantity += $quantity;
            $existing->unit_price = $unitPrice;
            $existing->save();
            $item = $existing->load(['product.primaryImage', 'productColor', 'lens', 'lensOption']);
            return response()->json([
                'message' => 'Đã cập nhật số lượng trong giỏ hàng.',
                'data' => new CartItemResource($item),
            ], 200);
        }

        $item = CartItem::create([
            'user_id' => $request->user()->id,
            'product_id' => $product->id,
            'product_color_id' => $productColorId,
            'lens_id' => $lensId,
            'lens_option_id' => $lensOptionId,
            'prescription_type' => $prescriptionData['type'] ?? null,
            'prescription_data' => $prescriptionData ?: null,
            'prescription_hash' => $prescriptionHash,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
        ]);

        $item->load(['product.primaryImage', 'productColor', 'lens', 'lensOption']);

        return response()->json([
            'message' => 'Đã thêm vào giỏ hàng.',
            'data' => new CartItemResource($item),
        ], 201);
    }

    /**
     * Cập nhật số lượng hoặc option của item trong giỏ.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $item = CartItem::query()
            ->where('user_id', $request->user()->id)
            ->where('id', $id)
            ->firstOrFail();

        $validated = $request->validate([
            'quantity' => 'nullable|integer|min:1|max:99',
            'product_color_id' => 'nullable|exists:product_colors,id',
            'lens_id' => 'nullable|exists:lenses,id',
            'lens_option_id' => 'nullable|exists:lens_options,id',
            'prescription_type' => 'nullable|string|in:myopia,hyperopia,reading,other',
            'prescription' => 'nullable|array',
            'prescription.right_sphere' => 'nullable|numeric|between:-30,30',
            'prescription.right_cylinder' => 'nullable|numeric|between:-10,10',
            'prescription.right_axis' => 'nullable|integer|between:0,180',
            'prescription.left_sphere' => 'nullable|numeric|between:-30,30',
            'prescription.left_cylinder' => 'nullable|numeric|between:-10,10',
            'prescription.left_axis' => 'nullable|integer|between:0,180',
            'prescription.pd' => 'nullable|numeric|between:40,80',
            'prescription.notes' => 'nullable|string|max:1000',
            'prescription.image_url' => 'nullable|url|max:500',
        ]);

        if (isset($validated['quantity'])) {
            $item->quantity = $validated['quantity'];
        }
        if (array_key_exists('product_color_id', $validated)) {
            if ($validated['product_color_id']) {
                ProductColor::where('id', $validated['product_color_id'])->where('product_id', $item->product_id)->firstOrFail();
            }
            $item->product_color_id = $validated['product_color_id'];
        }
        if (array_key_exists('lens_id', $validated)) {
            if ($validated['lens_id']) {
                Lens::where('id', $validated['lens_id'])->firstOrFail();
            }
            $item->lens_id = $validated['lens_id'];
            $item->lens_option_id = null;
        }
        if (array_key_exists('lens_option_id', $validated) && !array_key_exists('lens_id', $validated)) {
            if ($validated['lens_option_id']) {
                LensOption::where('id', $validated['lens_option_id'])->where('product_id', $item->product_id)->firstOrFail();
            }
            $item->lens_option_id = $validated['lens_option_id'];
            if ($validated['lens_option_id']) {
                $item->lens_id = null;
            }
        }
        if (array_key_exists('prescription', $validated) || array_key_exists('prescription_type', $validated)) {
            $prescriptionData = $this->normalizePrescriptionData(
                $validated['prescription'] ?? [],
                $validated['prescription_type'] ?? null
            );
            $item->prescription_type = $prescriptionData['type'] ?? null;
            $item->prescription_data = $prescriptionData ?: null;
            $item->prescription_hash = $this->makePrescriptionHash($prescriptionData);
        }

        $lens = $item->lens_id ? Lens::find($item->lens_id) : null;
        $this->ensurePrescriptionIsPresentIfRequired($lens, $item->prescription_data ?: []);

        $item->unit_price = $this->calculateUnitPrice($item->product, $item->product_color_id, $item->lens_id, $item->lens_option_id);
        $item->save();
        $item->load(['product.primaryImage', 'productColor', 'lens', 'lensOption']);

        return response()->json([
            'message' => 'Đã cập nhật giỏ hàng.',
            'data' => new CartItemResource($item),
        ]);
    }

    /**
     * Xóa một item khỏi giỏ hàng.
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $deleted = CartItem::query()
            ->where('user_id', $request->user()->id)
            ->where('id', $id)
            ->delete();

        if (!$deleted) {
            return response()->json(['message' => 'Không tìm thấy item trong giỏ hàng.'], 404);
        }

        return response()->json(['message' => 'Đã xóa khỏi giỏ hàng.']);
    }

    /**
     * Xóa toàn bộ giỏ hàng.
     */
    public function clear(Request $request): JsonResponse
    {
        CartItem::query()
            ->where('user_id', $request->user()->id)
            ->delete();

        return response()->json(['message' => 'Đã xóa toàn bộ giỏ hàng.']);
    }

    private function calculateUnitPrice(Product $product, ?int $productColorId, ?int $lensId, ?int $lensOptionId): float
    {
        $price = (float) $product->base_price;
        if ($productColorId) {
            $color = ProductColor::find($productColorId);
            if ($color) {
                $price += (float) $color->price_adjustment;
            }
        }
        if ($lensId) {
            $lens = Lens::find($lensId);
            if ($lens) {
                $price += (float) $lens->base_price;
            }
        } elseif ($lensOptionId) {
            $lens = LensOption::find($lensOptionId);
            if ($lens) {
                $price += (float) $lens->price_adjustment;
            }
        }
        return round($price, 2);
    }

    private function normalizePrescriptionData(array $prescription, ?string $type): array
    {
        $data = [];
        $type = $type ?: ($prescription['type'] ?? null);
        if ($type) {
            $data['type'] = $type;
        }

        foreach ([
            'right_sphere', 'right_cylinder', 'right_axis',
            'left_sphere', 'left_cylinder', 'left_axis',
            'pd',
        ] as $key) {
            if (array_key_exists($key, $prescription) && $prescription[$key] !== null && $prescription[$key] !== '') {
                $data[$key] = is_numeric($prescription[$key]) ? (float) $prescription[$key] : $prescription[$key];
            }
        }

        foreach (['notes', 'image_url'] as $key) {
            if (!empty($prescription[$key])) {
                $data[$key] = trim((string) $prescription[$key]);
            }
        }

        return $data;
    }

    private function makePrescriptionHash(array $prescriptionData): ?string
    {
        if (empty($prescriptionData)) {
            return null;
        }

        ksort($prescriptionData);
        return hash('sha256', json_encode($prescriptionData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    private function ensurePrescriptionIsPresentIfRequired(?Lens $lens, array $prescriptionData): void
    {
        if (!$lens || !$lens->requires_prescription) {
            return;
        }

        if (
            !array_key_exists('right_sphere', $prescriptionData)
            && !array_key_exists('left_sphere', $prescriptionData)
        ) {
            abort(response()->json([
                'message' => 'Vui lòng nhập độ mắt cho lens đã chọn.',
                'errors' => [
                    'prescription' => ['Lens này yêu cầu ít nhất độ cầu của mắt phải hoặc mắt trái.'],
                ],
            ], 422));
        }
    }
}
