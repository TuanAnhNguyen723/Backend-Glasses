<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PromoCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PromoCodeController extends Controller
{
    public function index()
    {
        return view('admin.promo-codes.index');
    }

    public function getPromoCodes(Request $request): JsonResponse
    {
        $perPage = max(1, min(50, (int) $request->get('per_page', 10)));
        $search = trim((string) $request->get('search', ''));
        $scope = $request->get('scope');
        $status = $request->get('status');

        $query = PromoCode::query()->with('product');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', '%' . $search . '%')
                    ->orWhere('name', 'like', '%' . $search . '%');
            });
        }
        if (in_array($scope, PromoCode::validScopes(), true)) {
            $query->where('scope', $scope);
        }
        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        $promoCodes = $query->orderByDesc('id')->paginate($perPage);

        return response()->json([
            'data' => $promoCodes->items(),
            'current_page' => $promoCodes->currentPage(),
            'last_page' => $promoCodes->lastPage(),
            'per_page' => $promoCodes->perPage(),
            'total' => $promoCodes->total(),
            'from' => $promoCodes->firstItem(),
            'to' => $promoCodes->lastItem(),
        ]);
    }

    public function getProducts(): JsonResponse
    {
        $products = Product::query()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->limit(200)
            ->get();

        return response()->json(['data' => $products]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validatePayload($request);

        $promoCode = PromoCode::create($validated);
        $promoCode->load('product');

        return response()->json([
            'success' => true,
            'message' => 'Đã tạo mã giảm giá.',
            'data' => $promoCode,
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $promoCode = PromoCode::findOrFail($id);
        $validated = $this->validatePayload($request, $promoCode->id);
        $promoCode->update($validated);
        $promoCode->load('product');

        return response()->json([
            'success' => true,
            'message' => 'Đã cập nhật mã giảm giá.',
            'data' => $promoCode,
        ]);
    }

    public function toggleStatus(Request $request, int $id): JsonResponse
    {
        $promoCode = PromoCode::findOrFail($id);

        if ($request->has('is_active')) {
            $promoCode->is_active = $request->boolean('is_active');
        } else {
            $promoCode->is_active = ! $promoCode->is_active;
        }
        $promoCode->save();

        return response()->json([
            'success' => true,
            'message' => 'Đã cập nhật trạng thái mã giảm giá.',
            'data' => $promoCode,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $promoCode = PromoCode::findOrFail($id);
        $promoCode->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa mã giảm giá.',
        ]);
    }

    private function validatePayload(Request $request, ?int $ignoreId = null): array
    {
        $codeRule = 'required|string|max:64|unique:promo_codes,code';
        if ($ignoreId) {
            $codeRule .= ',' . $ignoreId;
        }

        $validated = $request->validate([
            'code' => $codeRule,
            'name' => 'nullable|string|max:255',
            'scope' => 'required|string|in:' . implode(',', PromoCode::validScopes()),
            'product_id' => 'nullable|integer|exists:products,id',
            'discount_type' => 'required|string|in:' . implode(',', PromoCode::validDiscountTypes()),
            'discount_value' => 'required|numeric|min:0.01',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'nullable|boolean',
            'description' => 'nullable|string',
        ]);

        if (($validated['scope'] ?? null) === PromoCode::SCOPE_PRODUCT && empty($validated['product_id'])) {
            throw ValidationException::withMessages([
                'product_id' => 'Vui lòng chọn sản phẩm khi phạm vi áp dụng là theo sản phẩm.',
            ]);
        }

        if (($validated['scope'] ?? null) === PromoCode::SCOPE_ALL_PRODUCTS) {
            $validated['product_id'] = null;
        }

        $validated['code'] = strtoupper(trim((string) $validated['code']));
        $validated['min_order_amount'] = $validated['min_order_amount'] ?? 0;
        $validated['is_active'] = $validated['is_active'] ?? true;

        return $validated;
    }
}
