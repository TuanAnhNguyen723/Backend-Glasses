<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartItemResource;
use App\Models\CartItem;
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
            ->with(['product.primaryImage', 'productColor', 'lensOption'])
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
     * Body: product_id, quantity (optional), product_color_id (optional), lens_option_id (optional)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'nullable|integer|min:1|max:99',
            'product_color_id' => 'nullable|exists:product_colors,id',
            'lens_option_id' => 'nullable|exists:lens_options,id',
        ]);

        $quantity = (int) ($validated['quantity'] ?? 1);
        $product = Product::findOrFail($validated['product_id']);

        // Kiểm tra product_color và lens_option thuộc đúng product
        $productColorId = $validated['product_color_id'] ?? null;
        $lensOptionId = $validated['lens_option_id'] ?? null;

        if ($productColorId) {
            ProductColor::where('id', $productColorId)->where('product_id', $product->id)->firstOrFail();
        }
        if ($lensOptionId) {
            LensOption::where('id', $lensOptionId)->where('product_id', $product->id)->firstOrFail();
        }

        $unitPrice = $this->calculateUnitPrice($product, $productColorId, $lensOptionId);

        $existing = CartItem::query()
            ->where('user_id', $request->user()->id)
            ->where('product_id', $product->id)
            ->where('product_color_id', $productColorId)
            ->where('lens_option_id', $lensOptionId)
            ->first();

        if ($existing) {
            $existing->quantity += $quantity;
            $existing->unit_price = $unitPrice;
            $existing->save();
            $item = $existing->load(['product.primaryImage', 'productColor', 'lensOption']);
            return response()->json([
                'message' => 'Đã cập nhật số lượng trong giỏ hàng.',
                'data' => new CartItemResource($item),
            ], 200);
        }

        $item = CartItem::create([
            'user_id' => $request->user()->id,
            'product_id' => $product->id,
            'product_color_id' => $productColorId,
            'lens_option_id' => $lensOptionId,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
        ]);

        $item->load(['product.primaryImage', 'productColor', 'lensOption']);

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
            'lens_option_id' => 'nullable|exists:lens_options,id',
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
        if (array_key_exists('lens_option_id', $validated)) {
            if ($validated['lens_option_id']) {
                LensOption::where('id', $validated['lens_option_id'])->where('product_id', $item->product_id)->firstOrFail();
            }
            $item->lens_option_id = $validated['lens_option_id'];
        }

        $item->unit_price = $this->calculateUnitPrice($item->product, $item->product_color_id, $item->lens_option_id);
        $item->save();
        $item->load(['product.primaryImage', 'productColor', 'lensOption']);

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

    private function calculateUnitPrice(Product $product, ?int $productColorId, ?int $lensOptionId): float
    {
        $price = (float) $product->base_price;
        if ($productColorId) {
            $color = ProductColor::find($productColorId);
            if ($color) {
                $price += (float) $color->price_adjustment;
            }
        }
        if ($lensOptionId) {
            $lens = LensOption::find($lensOptionId);
            if ($lens) {
                $price += (float) $lens->price_adjustment;
            }
        }
        return round($price, 2);
    }
}
