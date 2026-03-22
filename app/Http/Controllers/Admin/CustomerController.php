<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Trang danh sách khách hàng.
     */
    public function index()
    {
        return view('admin.customers.index');
    }

    /**
     * Trang chi tiết khách hàng.
     */
    public function show(int $id)
    {
        $customer = User::withCount('orders')
            ->withSum(['orders' => fn ($q) => $q->whereNotIn('status', [Order::STATUS_CANCELLED])], 'total_amount')
            ->find($id);

        if (!$customer) {
            abort(404);
        }

        return view('admin.customers.show', ['customer' => $customer]);
    }

    /**
     * API danh sách khách hàng (phân trang, tìm kiếm).
     */
    public function getCustomers(Request $request): JsonResponse
    {
        $perPage = max(1, min(50, (int) $request->get('per_page', 10)));
        $search = trim((string) $request->get('search', ''));

        $query = User::query()
            ->select([
                'users.id',
                'users.name',
                'users.email',
                'users.phone',
                'users.avatar',
                'users.created_at',
            ])
            ->withCount('orders')
            ->withSum(
                ['orders' => fn ($q) => $q->whereNotIn('status', [Order::STATUS_CANCELLED])],
                'total_amount'
            )
            ->orderBy('created_at', 'desc');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        $customers = $query->paginate($perPage);

        $data = $customers->getCollection()->map(function (User $user) {
            $lastOrder = Order::where('user_id', $user->id)
                ->orderByDesc('created_at')
                ->first();

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'avatar' => $user->avatar,
                'orders_count' => $user->orders_count ?? 0,
                'total_spent' => (float) ($user->orders_sum_total_amount ?? 0),
                'total_spent_formatted' => number_format((float) ($user->orders_sum_total_amount ?? 0), 0),
                'last_order_at' => $lastOrder?->created_at->format('M d, Y'),
                'created_at' => $user->created_at->format('M d, Y'),
            ];
        });

        return response()->json([
            'data' => $data,
            'current_page' => $customers->currentPage(),
            'last_page' => $customers->lastPage(),
            'per_page' => $customers->perPage(),
            'total' => $customers->total(),
            'from' => $customers->firstItem(),
            'to' => $customers->lastItem(),
        ]);
    }

    /**
     * API chi tiết khách hàng + đơn hàng gần đây.
     */
    public function getCustomerDetail(int $id): JsonResponse
    {
        $customer = User::withCount('orders')
            ->withSum(['orders' => fn ($q) => $q->whereNotIn('status', [Order::STATUS_CANCELLED])], 'total_amount')
            ->find($id);

        if (!$customer) {
            return response()->json(['message' => 'Không tìm thấy khách hàng'], 404);
        }

        $recentOrders = Order::where('user_id', $customer->id)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get(['id', 'order_number', 'status', 'total_amount', 'payment_status', 'created_at'])
            ->map(fn (Order $o) => [
                'id' => $o->id,
                'order_number' => $o->order_number,
                'status' => $o->status,
                'status_label' => $this->statusLabel($o->status),
                'total_amount' => (float) $o->total_amount,
                'total_formatted' => number_format((float) $o->total_amount, 0),
                'payment_status' => $o->payment_status ?? 'pending',
                'created_at' => $o->created_at->format('M d, Y H:i'),
            ]);

        return response()->json([
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'avatar' => $customer->avatar,
                'date_of_birth' => $customer->date_of_birth?->format('Y-m-d'),
                'gender' => $customer->gender,
                'orders_count' => $customer->orders_count ?? 0,
                'total_spent' => (float) ($customer->orders_sum_total_amount ?? 0),
                'total_spent_formatted' => number_format((float) ($customer->orders_sum_total_amount ?? 0), 0),
                'created_at' => $customer->created_at->format('M d, Y H:i'),
            ],
            'recent_orders' => $recentOrders,
        ]);
    }

    private function statusLabel(string $status): string
    {
        $map = [
            'pending' => 'Chờ xử lý',
            'completed' => 'Đã đặt/Thanh toán xong',
            'confirmed' => 'Đã xác nhận',
            'processing' => 'Đang xử lý',
            'shipped' => 'Đang giao hàng',
            'delivered' => 'Đã giao',
            'cancelled' => 'Đã hủy',
        ];
        return $map[$status] ?? ucfirst($status);
    }
}
