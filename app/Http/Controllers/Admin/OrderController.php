<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
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

        $query = Order::with(['user', 'items.product.primaryImage'])
            ->orderBy('created_at', 'desc');

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
                if (! empty($primary->image_url)) {
                    $productImageUrl = $primary->image_url;
                } elseif (! empty($primary->image_path)) {
                    try {
                        $productImageUrl = Storage::disk('backblaze')->temporaryUrl($primary->image_path, now()->addWeek());
                    } catch (\Throwable $e) {
                        $productImageUrl = null;
                    }
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
                'product_name' => $productName,
                'product_image_url' => $productImageUrl,
                'status' => $order->status,
                'status_display' => $statusDisplay['label'],
                'status_class' => $statusDisplay['class'],
                'payment_display' => $paymentDisplay['label'],
                'payment_class' => $paymentDisplay['class'],
                'total_amount' => (float) $order->total_amount,
                'total_formatted' => number_format((float) $order->total_amount, 2),
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

        $order = Order::findOrFail($id);
        $oldStatus = $order->status;
        $newStatus = $request->input('status');

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
}
