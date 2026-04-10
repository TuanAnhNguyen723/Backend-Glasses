<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use App\Models\UserPromoCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PromoCodeController extends Controller
{
    /**
     * Danh sách voucher đang khả dụng để user claim (kiểu Shopee).
     */
    public function available(Request $request): JsonResponse
    {
        $request->validate([
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'integer|exists:products,id',
        ]);

        $user = $request->user();
        $productIds = array_values(array_unique(array_map(
            fn ($id): int => (int) $id,
            $request->input('product_ids', [])
        )));
        $promoCodes = PromoCode::query()
            ->with('product:id,name')
            ->where('is_active', true)
            ->orderByDesc('id')
            ->get()
            ->filter(function (PromoCode $promo) use ($productIds) {
                if (! $promo->isAvailable()) {
                    return false;
                }

                // Voucher theo sản phẩm: chỉ hiển thị khi checkout có đúng sản phẩm áp dụng.
                if ($promo->scope === PromoCode::SCOPE_PRODUCT) {
                    if (empty($productIds)) {
                        return false;
                    }

                    return in_array((int) $promo->product_id, $productIds, true);
                }

                return true;
            })
            ->values();

        $claimedIds = UserPromoCode::query()
            ->where('user_id', $user->id)
            ->pluck('promo_code_id')
            ->all();

        return response()->json([
            'data' => $promoCodes->map(function (PromoCode $promo) use ($claimedIds) {
                return [
                    'id' => $promo->id,
                    'code' => $promo->code,
                    'name' => $promo->name,
                    'description' => $promo->description,
                    'scope' => $promo->scope,
                    'product_id' => $promo->product_id,
                    'product_name' => $promo->product?->name,
                    'discount_type' => $promo->discount_type,
                    'discount_value' => (float) $promo->discount_value,
                    'min_order_amount' => (float) $promo->min_order_amount,
                    'max_discount_amount' => $promo->max_discount_amount !== null ? (float) $promo->max_discount_amount : null,
                    'usage_limit' => $promo->usage_limit,
                    'used_count' => $promo->used_count,
                    'starts_at' => $promo->starts_at?->toIso8601String(),
                    'ends_at' => $promo->ends_at?->toIso8601String(),
                    'is_claimed' => in_array($promo->id, $claimedIds, true),
                ];
            }),
        ]);
    }

    /**
     * Claim voucher.
     */
    public function claim(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $promo = PromoCode::findOrFail($id);

        if (! $promo->isAvailable()) {
            return response()->json([
                'message' => 'Voucher đã hết hiệu lực hoặc không còn lượt dùng.',
            ], 422);
        }

        $existing = UserPromoCode::query()
            ->where('user_id', $user->id)
            ->where('promo_code_id', $promo->id)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Bạn đã claim voucher này trước đó.',
            ], 422);
        }

        UserPromoCode::create([
            'user_id' => $user->id,
            'promo_code_id' => $promo->id,
            'claimed_at' => now(),
        ]);

        return response()->json([
            'message' => 'Claim voucher thành công.',
        ]);
    }

    /**
     * Danh sách voucher user đã claim.
     */
    public function myVouchers(Request $request): JsonResponse
    {
        $request->validate([
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'integer|exists:products,id',
        ]);

        $productIds = array_values(array_unique(array_map(
            fn ($id): int => (int) $id,
            $request->input('product_ids', [])
        )));
        $claims = UserPromoCode::query()
            ->with(['promoCode.product:id,name'])
            ->where('user_id', $request->user()->id)
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'data' => $claims->map(function (UserPromoCode $claim) use ($productIds) {
                $promo = $claim->promoCode;
                if (! $promo) {
                    return null;
                }

                if (
                    $promo->scope === PromoCode::SCOPE_PRODUCT
                    && ! in_array((int) $promo->product_id, $productIds, true)
                ) {
                    return null;
                }

                return [
                    'claim_id' => $claim->id,
                    'claimed_at' => $claim->claimed_at?->toIso8601String(),
                    'used_at' => $claim->used_at?->toIso8601String(),
                    'order_id' => $claim->order_id,
                    'promo' => [
                        'id' => $promo->id,
                        'code' => $promo->code,
                        'name' => $promo->name,
                        'description' => $promo->description,
                        'scope' => $promo->scope,
                        'product_id' => $promo->product_id,
                        'product_name' => $promo->product?->name,
                        'discount_type' => $promo->discount_type,
                        'discount_value' => (float) $promo->discount_value,
                        'min_order_amount' => (float) $promo->min_order_amount,
                        'max_discount_amount' => $promo->max_discount_amount !== null ? (float) $promo->max_discount_amount : null,
                        'is_active' => (bool) $promo->is_active,
                        'starts_at' => $promo->starts_at?->toIso8601String(),
                        'ends_at' => $promo->ends_at?->toIso8601String(),
                        'is_available_now' => $promo->isAvailable(),
                    ],
                ];
            })->filter()->values(),
        ]);
    }
}
