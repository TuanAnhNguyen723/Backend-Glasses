<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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

        $query = Order::with(['user', 'items'])
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
            $productImageUrl = $firstItem ? $firstItem->product_image_url : null;

            $paymentStatus = $order->payment_status ?? ($order->status === 'cancelled' ? 'refunded' : 'pending');
            $paymentDisplay = $this->paymentDisplay($paymentStatus);
            $statusDisplay = $this->statusDisplay($order->status);

            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
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
            'current_page' => $orders->currentPage(),
            'last_page' => $orders->lastPage(),
            'per_page' => $orders->perPage(),
            'total' => $orders->total(),
            'from' => $orders->firstItem(),
            'to' => $orders->lastItem(),
        ]);
    }

    private function statusDisplay(string $status): array
    {
        $map = [
            'pending' => ['label' => 'Pending', 'class' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'],
            'confirmed' => ['label' => 'Processing', 'class' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'],
            'processing' => ['label' => 'Processing', 'class' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'],
            'shipped' => ['label' => 'Shipped', 'class' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400'],
            'delivered' => ['label' => 'Delivered', 'class' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'],
            'cancelled' => ['label' => 'Cancelled', 'class' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'],
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
