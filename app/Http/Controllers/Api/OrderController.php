<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\ProductColor;
use App\Models\LensOption;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    /**
     * Danh sách đơn hàng của user (phân trang).
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = max(1, min(50, (int) $request->get('per_page', 15)));
        $orders = Order::query()
            ->where('user_id', $request->user()->id)
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'data' => OrderResource::collection($orders->items()),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    /**
     * Chi tiết một đơn hàng.
     */
    public function show(Request $request, $id): JsonResponse
    {
        $order = Order::query()
            ->where('user_id', $request->user()->id)
            ->with(['items', 'statusHistory'])
            ->findOrFail($id);

        return response()->json(['data' => new OrderResource($order)]);
    }

    /**
     * Tạo đơn hàng mới (guest hoặc user đã đăng nhập).
     * Guest: chỉ được gửi items[] (không có cart_item_ids).
     * User: có thể gửi cart_item_ids (từ giỏ) hoặc items[].
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'shipping_name' => 'required|string|max:255',
            'shipping_phone' => 'required|string|max:20',
            'shipping_email' => 'required|email',
            'shipping_address' => 'required|string',
            'shipping_city' => 'nullable|string|max:100',
            'shipping_postal_code' => 'nullable|string|max:20',
            'shipping_country' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
            'promo_code' => 'nullable|string|max:64',
            'payment_method' => 'nullable|string|in:cod,bank_transfer,momo,vnpay,paypal,other',
            'payment_status' => 'nullable|string|in:pending,paid',
            'payment_reference' => 'nullable|string|max:255',
            'cart_item_ids' => 'nullable|array',
            'cart_item_ids.*' => 'integer|exists:cart_items,id',
            'items' => 'nullable|array',
            'items.*.product_id' => 'required_with:items|exists:products,id',
            'items.*.quantity' => 'required_with:items|integer|min:1|max:99',
            'items.*.product_color_id' => 'nullable|exists:product_colors,id',
            'items.*.lens_option_id' => 'nullable|exists:lens_options,id',
        ]);

        $userId = auth()->id(); // null nếu guest

        if (! empty($validated['cart_item_ids'])) {
            if (! $userId) {
                return response()->json(['message' => 'Khách chưa đăng nhập chỉ được tạo đơn bằng items, không dùng cart_item_ids.'], 422);
            }
            $cartItems = CartItem::query()
                ->where('user_id', $userId)
                ->whereIn('id', $validated['cart_item_ids'])
                ->with(['product.primaryImage', 'productColor', 'lensOption'])
                ->get();
            if ($cartItems->isEmpty()) {
                return response()->json(['message' => 'Không có sản phẩm nào trong giỏ được chọn.'], 422);
            }
            $orderItemsData = $cartItems->map(fn (CartItem $c) => [
                'product_id' => $c->product_id,
                'product_name' => $c->product->name,
                'product_color_name' => $c->productColor?->name,
                'lens_option_name' => $c->lensOption?->name,
                'quantity' => $c->quantity,
                'unit_price' => (float) $c->unit_price,
                'product_image_url' => $this->getProductPrimaryImageUrl($c->product),
            ])->all();
        } elseif (! empty($validated['items'])) {
            $orderItemsData = [];
            foreach ($validated['items'] as $row) {
                $product = Product::findOrFail($row['product_id']);
                $colorId = $row['product_color_id'] ?? null;
                $lensId = $row['lens_option_id'] ?? null;
                if ($colorId) {
                    ProductColor::where('id', $colorId)->where('product_id', $product->id)->firstOrFail();
                }
                if ($lensId) {
                    LensOption::where('id', $lensId)->where('product_id', $product->id)->firstOrFail();
                }
                $unitPrice = $this->calculateUnitPrice($product, $colorId, $lensId);
                $color = $colorId ? ProductColor::find($colorId) : null;
                $lens = $lensId ? LensOption::find($lensId) : null;
                $orderItemsData[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_color_name' => $color?->name,
                    'lens_option_name' => $lens?->name,
                    'quantity' => (int) $row['quantity'],
                    'unit_price' => $unitPrice,
                    'product_image_url' => $this->getProductPrimaryImageUrl($product),
                ];
            }
        } else {
            return response()->json([
                'message' => 'Vui lòng gửi cart_item_ids (khi đã đăng nhập) hoặc items (danh sách sản phẩm).',
            ], 422);
        }

        $subtotal = 0;
        foreach ($orderItemsData as $row) {
            $subtotal += $row['unit_price'] * $row['quantity'];
        }
        $subtotal = round($subtotal, 2);
        $discountAmount = $this->resolvePromoDiscount($validated['promo_code'] ?? null, $subtotal);
        $shippingAmount = 0; // Có thể tính theo địa chỉ / quy tắc
        $taxAmount = 0;
        $totalAmount = round($subtotal - $discountAmount + $shippingAmount + $taxAmount, 2);

        // Trạng thái từ frontend: paid (Momo/VNPay) → confirmed, pending (trực tiếp) → pending
        $paymentStatus = $validated['payment_status'] ?? 'pending';
        $orderStatus = $paymentStatus === 'paid' ? 'confirmed' : 'pending';

        DB::beginTransaction();
        try {
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $userId,
                'status' => $orderStatus,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'shipping_amount' => $shippingAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'promo_code' => $validated['promo_code'] ?? null,
                'shipping_name' => $validated['shipping_name'],
                'shipping_phone' => $validated['shipping_phone'],
                'shipping_email' => $validated['shipping_email'],
                'shipping_address' => $validated['shipping_address'],
                'shipping_city' => $validated['shipping_city'] ?? null,
                'shipping_postal_code' => $validated['shipping_postal_code'] ?? null,
                'shipping_country' => $validated['shipping_country'] ?? 'Vietnam',
                'notes' => $validated['notes'] ?? null,
                'payment_method' => $validated['payment_method'] ?? null,
                'payment_status' => $paymentStatus,
                'payment_reference' => $validated['payment_reference'] ?? null,
                'paid_at' => $paymentStatus === 'paid' ? now() : null,
            ]);

            foreach ($orderItemsData as $row) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $row['product_id'],
                    'product_name' => $row['product_name'],
                    'product_color_name' => $row['product_color_name'],
                    'lens_option_name' => $row['lens_option_name'],
                    'quantity' => $row['quantity'],
                    'unit_price' => $row['unit_price'],
                    'total_price' => round($row['unit_price'] * $row['quantity'], 2),
                    'product_image_url' => $row['product_image_url'],
                ]);
            }

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => $orderStatus,
                'message' => $paymentStatus === 'paid'
                    ? 'Đơn hàng đã được tạo (đã thanh toán' . ($validated['payment_method'] ?? '' ? ' qua ' . $validated['payment_method'] : '') . ').'
                    : 'Đơn hàng đã được tạo.',
                'created_by' => $userId,
            ]);

            if ($userId && ! empty($validated['cart_item_ids'])) {
                CartItem::query()
                    ->where('user_id', $userId)
                    ->whereIn('id', $validated['cart_item_ids'])
                    ->delete();
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        $order->load(['items', 'statusHistory']);

        return response()->json([
            'message' => 'Đơn hàng đã được tạo thành công.',
            'data' => new OrderResource($order),
        ], 201);
    }

    /**
     * Hủy đơn hàng (chỉ khi status = pending).
     */
    public function cancel(Request $request, $id): JsonResponse
    {
        $order = Order::query()
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        if ($order->status !== 'pending') {
            return response()->json([
                'message' => 'Chỉ có thể hủy đơn hàng đang ở trạng thái chờ xử lý.',
            ], 422);
        }

        $order->status = 'cancelled';
        if ($order->payment_status === 'paid') {
            $order->payment_status = 'refunded';
        }
        $order->save();

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'status' => 'cancelled',
            'message' => 'Khách hàng đã hủy đơn hàng.',
            'created_by' => $request->user()->id,
        ]);

        $order->load(['items', 'statusHistory']);

        return response()->json([
            'message' => 'Đơn hàng đã được hủy.',
            'data' => new OrderResource($order),
        ]);
    }

    /**
     * Theo dõi đơn hàng (trạng thái + lịch sử).
     */
    public function track(Request $request, $id): JsonResponse
    {
        $order = Order::query()
            ->where('user_id', $request->user()->id)
            ->with(['items', 'statusHistory'])
            ->findOrFail($id);

        return response()->json([
            'data' => [
                'order_number' => $order->order_number,
                'status' => $order->status,
                'tracking_number' => $order->tracking_number,
                'estimated_delivery_date' => $order->estimated_delivery_date?->format('Y-m-d'),
                'delivered_at' => $order->delivered_at?->toIso8601String(),
                'status_history' => $order->statusHistory->map(fn ($h) => [
                    'status' => $h->status,
                    'message' => $h->message,
                    'created_at' => $h->created_at?->toIso8601String(),
                ])->values()->all(),
            ],
        ]);
    }

    /**
     * Lưu thông tin thanh toán cho đơn hàng (user đã đăng nhập).
     * Frontend: Momo/VNPay demo gọi ngay sau redirect success; thanh toán trực tiếp gọi khi user bấm "Xác nhận đã thanh toán".
     */
    public function recordPayment(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'payment_method' => 'required|string|in:cod,bank_transfer,momo,vnpay,paypal,other',
            'payment_reference' => 'nullable|string|max:255',
        ]);

        $order = Order::query()
            ->where('user_id', $request->user()->id)
            ->where('id', $id)
            ->firstOrFail();

        return $this->applyPaymentToOrder($order, $validated, $request->user()->id);
    }

    /**
     * Ghi nhận thanh toán cho đơn guest (sau redirect Momo/VNPay).
     * Public, xác thực bằng order_number + shipping_email.
     */
    public function recordPaymentGuest(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_number' => 'required|string|max:32',
            'shipping_email' => 'required|email',
            'payment_method' => 'required|string|in:cod,bank_transfer,momo,vnpay,paypal,other',
            'payment_reference' => 'nullable|string|max:255',
        ]);

        $order = Order::query()
            ->whereNull('user_id')
            ->where('order_number', $validated['order_number'])
            ->where('shipping_email', $validated['shipping_email'])
            ->firstOrFail();

        return $this->applyPaymentToOrder($order, $validated, null);
    }

    /**
     * Áp dụng trạng thái thanh toán lên đơn (dùng chung cho user và guest).
     */
    private function applyPaymentToOrder(Order $order, array $validated, ?int $createdBy): JsonResponse
    {
        if ($order->payment_status === 'paid') {
            return response()->json([
                'message' => 'Đơn hàng này đã được ghi nhận thanh toán trước đó.',
            ], 422);
        }

        $order->payment_status = 'paid';
        $order->payment_method = $validated['payment_method'];
        $order->payment_reference = $validated['payment_reference'] ?? null;
        $order->paid_at = now();
        if ($order->status === 'pending') {
            $order->status = 'confirmed';
        }
        $order->save();

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'status' => $order->status,
            'message' => 'Đã thanh toán (' . $validated['payment_method'] . ')' . (! empty($validated['payment_reference']) ? ' - Mã: ' . ($validated['payment_reference'] ?? '') : ''),
            'created_by' => $createdBy,
        ]);

        $order->load(['items', 'statusHistory']);

        return response()->json([
            'message' => 'Đã lưu thông tin thanh toán.',
            'data' => new OrderResource($order),
        ]);
    }

    /**
     * Validate mã giảm giá (placeholder: chưa có bảng promo_codes).
     */
    public function validatePromoCode(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|max:64',
            'subtotal' => 'nullable|numeric|min:0',
        ]);

        $subtotal = (float) ($request->input('subtotal', 0));
        // TODO: khi có bảng promo_codes thì check code và tính discount
        return response()->json([
            'data' => [
                'valid' => false,
                'message' => 'Mã giảm giá không tồn tại hoặc đã hết hạn.',
                'discount_amount' => 0,
                'code' => $request->input('code'),
            ],
        ]);
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

    private function resolvePromoDiscount(?string $promoCode, float $subtotal): float
    {
        if (empty($promoCode)) {
            return 0;
        }
        // TODO: khi có bảng promo_codes
        return 0;
    }

    private function getProductPrimaryImageUrl(Product $product): ?string
    {
        $primary = $product->primaryImage;
        if (!$primary) {
            return null;
        }
        if (!empty($primary->image_path)) {
            try {
                return Storage::disk('backblaze')->temporaryUrl($primary->image_path, now()->addWeek());
            } catch (\Exception $e) {
                return $primary->image_url ?? null;
            }
        }
        return $primary->image_url ?? null;
    }
}
