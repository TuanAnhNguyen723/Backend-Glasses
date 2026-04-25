<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    private const DELETABLE_STATUSES = [
        Order::STATUS_DELIVERED,
        Order::STATUS_CANCELLED,
    ];
    /**
     * Trang quản lý đơn hàng (chỉ view).
     */
    public function index()
    {
        return view('admin.orders.index');
    }

    /**
     * Chi tiết đơn hàng (placeholder - có thể mở rộng sau).
     */
    public function show(int $id)
    {
        $order = Order::with(['user', 'items'])->find($id);
        if (!$order) {
            abort(404);
        }
        return view('admin.orders.show', ['order' => $order]);
    }

    /**
     * API danh sách đơn hàng (phân trang, lọc, tìm kiếm).
     */
    public function getOrders(Request $request): JsonResponse
    {
        $perPage = max(1, min(50, (int) $request->get('per_page', 10)));
        $search = trim((string) $request->get('search', ''));
        $statusFilter = $request->get('status', 'all'); // all | pending | completed | cancelled
        $customerId = $request->get('customer'); // filter by user_id

        $query = Order::with(['user', 'items.product.primaryImage'])
            ->orderBy('created_at', 'desc');

        if ($customerId && is_numeric($customerId)) {
            $query->where('user_id', (int) $customerId);
        }

        if ($statusFilter === 'pending') {
            $query->where('status', 'pending');
        } elseif ($statusFilter === 'completed') {
            $query->whereIn('status', ['shipped', 'delivered']);
        } elseif ($statusFilter === 'cancelled') {
            $query->where('status', 'cancelled');
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', '%' . $search . '%')
                    ->orWhere('shipping_name', 'like', '%' . $search . '%')
                    ->orWhere('shipping_email', 'like', '%' . $search . '%')
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    });
            });
        }

        $orders = $query->paginate($perPage);

        $data = $orders->getCollection()->map(function (Order $order) {
            $firstItem = $order->items->first();
            $customerName = $order->user ? $order->user->name : $order->shipping_name;
            $customerEmail = $order->user ? $order->user->email : $order->shipping_email;
            $productName = $firstItem ? $firstItem->product_name : '—';
            // Không dùng signed URL đã lưu trong order_items (có thể hết hạn).
            // Thay vào đó lấy từ product.primaryImage để tạo signed URL mới (nếu có image_path),
            // hoặc dùng image_url tĩnh (nếu có).
            $productImageUrl = null;
            if ($firstItem && $firstItem->product && $firstItem->product->primaryImage) {
                $primary = $firstItem->product->primaryImage;
                // Ưu tiên image_path để luôn tạo được signed URL mới (tránh URL cũ hết hạn).
                if (! empty($primary->image_path)) {
                    try {
                        $productImageUrl = Storage::disk('backblaze')->temporaryUrl($primary->image_path, now()->addWeek());
                    } catch (\Throwable $e) {
                        $productImageUrl = null;
                    }
                }
                // Fallback khi không có image_path (ảnh public hoặc dữ liệu cũ).
                if (! $productImageUrl && ! empty($primary->image_url)) {
                    $productImageUrl = $primary->image_url;
                }
            }
            // Fallback cuối: dùng url đã lưu (nếu có) cho trường hợp ảnh public.
            if (! $productImageUrl && $firstItem) {
                $productImageUrl = $firstItem->product_image_url;
            }

            $paymentStatus = $order->payment_status ?? ($order->status === 'cancelled' ? 'refunded' : 'pending');
            $paymentDisplay = $this->paymentDisplay($paymentStatus);
            $statusDisplay = $this->statusDisplay($order->status);

            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer_id' => $order->user_id,
                'customer_name' => $customerName,
                'customer_email' => $customerEmail,
                'shipping_phone' => $order->shipping_phone,
                'shipping_address' => $order->shipping_address,
                'product_name' => $productName,
                'product_image_url' => $productImageUrl,
                'status' => $order->status,
                'status_display' => $statusDisplay['label'],
                'status_class' => $statusDisplay['class'],
                'payment_display' => $paymentDisplay['label'],
                'payment_class' => $paymentDisplay['class'],
                'total_amount' => (float) $order->total_amount,
                'total_formatted' => number_format((float) $order->total_amount, 0),
                'created_at' => $order->created_at->format('M d, Y'),
            ];
        });

        return response()->json([
            'data' => $data,
            'status_options' => Order::statusOptions(),
            'current_page' => $orders->currentPage(),
            'last_page' => $orders->lastPage(),
            'per_page' => $orders->perPage(),
            'total' => $orders->total(),
            'from' => $orders->firstItem(),
            'to' => $orders->lastItem(),
        ]);
    }

    /**
     * API danh sách trạng thái đơn (cho dropdown đổi trạng thái).
     */
    public function getStatusOptions(Request $request): JsonResponse
    {
        return response()->json(['data' => Order::statusOptions()]);
    }

    /**
     * Cập nhật trạng thái đơn (dropdown). Khi chuyển sang delivered thì ghi delivered_at.
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|string|in:' . implode(',', Order::validStatuses()),
        ]);

        $order = Order::with('items')->findOrFail($id);
        $oldStatus = $order->status;
        $newStatus = $request->input('status');

        // Trừ tồn kho đúng một lần tại mốc giao hàng thành công.
        if ($oldStatus !== Order::STATUS_DELIVERED && $newStatus === Order::STATUS_DELIVERED) {
            $this->decreaseStockForDeliveredOrder($order);
        }

        $order->status = $newStatus;
        if ($newStatus === Order::STATUS_DELIVERED && ! $order->delivered_at) {
            $order->delivered_at = now();
        }
        $order->save();

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'status' => $newStatus,
            'message' => sprintf('Trạng thái đổi từ %s sang %s.', $oldStatus, $newStatus),
            'created_by' => Auth::check() ? Auth::id() : null,
        ]);

        $order->load(['user', 'items']);
        $statusDisplay = $this->statusDisplay($order->status);

        return response()->json([
            'message' => 'Đã cập nhật trạng thái đơn.',
            'data' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'status_display' => $statusDisplay['label'],
                'status_class' => $statusDisplay['class'],
            ],
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $order = Order::findOrFail($id);

        if (! $this->canDeleteOrder($order)) {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể xóa đơn hàng ở trạng thái đã giao hoặc đã hủy.',
            ], 409);
        }

        DB::transaction(function () use ($order) {
            $order->statusHistory()->delete();
            $order->items()->delete();
            $order->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Đơn hàng đã được xóa thành công.',
        ]);
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_ids' => 'required|array|min:1',
            'order_ids.*' => 'integer|distinct|exists:orders,id',
        ]);

        $orders = Order::query()
            ->whereIn('id', $validated['order_ids'])
            ->get()
            ->keyBy('id');

        $deletedIds = [];
        $failed = [];

        foreach ($validated['order_ids'] as $orderId) {
            $order = $orders->get($orderId);
            if (! $order) {
                $failed[] = [
                    'id' => $orderId,
                    'message' => 'Không tìm thấy đơn hàng.',
                ];
                continue;
            }

            if (! $this->canDeleteOrder($order)) {
                $failed[] = [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'message' => 'Chỉ đơn đã giao hoặc đã hủy mới được xóa.',
                ];
                continue;
            }

            try {
                DB::transaction(function () use ($order) {
                    $order->statusHistory()->delete();
                    $order->items()->delete();
                    $order->delete();
                });
                $deletedIds[] = $order->id;
            } catch (\Throwable $e) {
                $failed[] = [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'message' => 'Xóa thất bại, vui lòng thử lại.',
                ];
            }
        }

        $deletedCount = count($deletedIds);
        $failedCount = count($failed);

        if ($deletedCount > 0 && $failedCount === 0) {
            $statusCode = 200;
            $success = true;
            $message = 'Đã xóa tất cả đơn hàng đã chọn.';
        } elseif ($deletedCount > 0 && $failedCount > 0) {
            $statusCode = 200;
            $success = true;
            $message = 'Đã xóa một phần đơn hàng đã chọn.';
        } else {
            $statusCode = 409;
            $success = false;
            $message = 'Không thể xóa các đơn hàng đã chọn.';
        }

        return response()->json([
            'success' => $success,
            'message' => $message,
            'deleted_count' => $deletedCount,
            'failed_count' => $failedCount,
            'deleted_ids' => $deletedIds,
            'failed_items' => $failed,
        ], $statusCode);
    }

    /**
     * Trừ tồn kho product theo số lượng đã mua khi đơn được giao thành công.
     */
    private function decreaseStockForDeliveredOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $product = Product::query()
                    ->where('id', $item->product_id)
                    ->lockForUpdate()
                    ->first();

                if (! $product) {
                    throw ValidationException::withMessages([
                        'status' => "Không tìm thấy sản phẩm #{$item->product_id} để trừ tồn kho.",
                    ]);
                }

                $newStock = (int) $product->stock_quantity - (int) $item->quantity;
                if ($newStock < 0) {
                    throw ValidationException::withMessages([
                        'status' => "Tồn kho không đủ cho sản phẩm {$product->name}.",
                    ]);
                }

                $product->stock_quantity = $newStock;
                $product->save();
            }
        });
    }

    private function statusDisplay(string $status): array
    {
        $map = [
            'pending' => ['label' => 'Chờ xử lý', 'class' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'],
            'completed' => ['label' => 'Đã đặt/Thanh toán xong', 'class' => 'bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-400'],
            'confirmed' => ['label' => 'Đã xác nhận', 'class' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'],
            'processing' => ['label' => 'Đang xử lý/Đóng gói', 'class' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'],
            'shipped' => ['label' => 'Đang giao hàng', 'class' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400'],
            'delivered' => ['label' => 'Đã giao', 'class' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'],
            'cancelled' => ['label' => 'Đã hủy', 'class' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'],
        ];
        return $map[$status] ?? ['label' => ucfirst($status), 'class' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400'];
    }

    private function paymentDisplay(string $paymentStatus): array
    {
        $map = [
            'pending' => ['label' => 'Pending', 'class' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'],
            'paid' => ['label' => 'Paid', 'class' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'],
            'refunded' => ['label' => 'Refunded', 'class' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400'],
            'failed' => ['label' => 'Failed', 'class' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'],
        ];
        return $map[$paymentStatus] ?? $map['pending'];
    }

    private function canDeleteOrder(Order $order): bool
    {
        return in_array($order->status, self::DELETABLE_STATUSES, true);
    }
}
