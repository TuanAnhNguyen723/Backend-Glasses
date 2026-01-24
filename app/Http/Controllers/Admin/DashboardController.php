<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard');
    }

    public function getStats()
    {
        // Tính tổng doanh thu từ các đơn hàng đã giao
        $totalRevenue = Order::where('status', 'delivered')
            ->sum('total_amount');

        // Tổng số đơn hàng
        $totalOrders = Order::count();

        // Số khách hàng đang hoạt động (có đơn hàng trong 30 ngày qua)
        $activeCustomers = User::whereHas('orders', function($query) {
            $query->where('created_at', '>=', Carbon::now()->subDays(30));
        })->count();

        // Tổng số sản phẩm đã bán (từ order_items)
        $productsSold = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', '!=', 'cancelled')
            ->sum('order_items.quantity');

        // Tính % tăng trưởng so với tháng trước
        $lastMonthRevenue = Order::where('status', 'delivered')
            ->whereYear('created_at', Carbon::now()->subMonth()->year)
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->sum('total_amount');
        
        $revenueGrowth = $lastMonthRevenue > 0 
            ? round((($totalRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
            : 0;

        $lastMonthOrders = Order::whereYear('created_at', Carbon::now()->subMonth()->year)
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->count();
        $ordersGrowth = $lastMonthOrders > 0 
            ? round((($totalOrders - $lastMonthOrders) / $lastMonthOrders) * 100, 1)
            : 0;

        // Tính % tăng trưởng khách hàng
        $lastMonthCustomers = User::whereHas('orders', function($query) {
            $query->whereYear('created_at', Carbon::now()->subMonth()->year)
                  ->whereMonth('created_at', Carbon::now()->subMonth()->month);
        })->count();
        $customersGrowth = $lastMonthCustomers > 0 
            ? round((($activeCustomers - $lastMonthCustomers) / $lastMonthCustomers) * 100, 1)
            : 0;

        // Tính % tăng trưởng sản phẩm đã bán
        $lastMonthProductsSold = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', '!=', 'cancelled')
            ->whereYear('orders.created_at', Carbon::now()->subMonth()->year)
            ->whereMonth('orders.created_at', Carbon::now()->subMonth()->month)
            ->sum('order_items.quantity');
        $productsSoldGrowth = $lastMonthProductsSold > 0 
            ? round((($productsSold - $lastMonthProductsSold) / $lastMonthProductsSold) * 100, 1)
            : 0;

        return response()->json([
            'total_revenue' => $totalRevenue,
            'total_orders' => $totalOrders,
            'active_customers' => $activeCustomers,
            'products_sold' => $productsSold ?? 0,
            'revenue_growth' => $revenueGrowth,
            'orders_growth' => $ordersGrowth,
            'customers_growth' => $customersGrowth,
            'products_sold_growth' => $productsSoldGrowth,
        ]);
    }

    public function getSalesOverview()
    {
        // Lấy dữ liệu doanh thu 6 tháng gần nhất
        $months = [];
        $revenues = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthName = $month->format('M');
            $revenue = Order::where('status', 'delivered')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('total_amount');

            $months[] = $monthName;
            $revenues[] = $revenue;
        }

        return response()->json([
            'months' => $months,
            'revenues' => $revenues,
        ]);
    }

    public function getCategoryStats()
    {
        // Thống kê sản phẩm theo category
        $categories = Category::withCount('products')->get();
        
        $totalProducts = Product::count();
        
        $categoryData = $categories->map(function($category) use ($totalProducts) {
            $percentage = $totalProducts > 0 
                ? round(($category->products_count / $totalProducts) * 100, 1)
                : 0;
            
            return [
                'name' => $category->name,
                'count' => $category->products_count,
                'percentage' => $percentage,
            ];
        });

        return response()->json([
            'total_products' => $totalProducts,
            'categories' => $categoryData,
        ]);
    }

    public function getRecentOrders()
    {
        $orders = Order::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function($order) {
                return [
                    'id' => $order->order_number,
                    'customer' => $order->user ? $order->user->name : 'Khách vãng lai',
                    'customer_initials' => $this->getInitials($order->user ? $order->user->name : 'KV'),
                    'status' => $order->status,
                    'status_label' => $this->getStatusLabel($order->status),
                    'amount' => number_format($order->total_amount, 0, ',', '.'),
                    'date' => $order->created_at->format('d M, Y'),
                ];
            });

        return response()->json($orders);
    }

    private function getInitials($name)
    {
        $words = explode(' ', $name);
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[count($words) - 1], 0, 1));
        }
        return strtoupper(substr($name, 0, 2));
    }

    private function getStatusLabel($status)
    {
        $labels = [
            'pending' => 'Đang chờ',
            'processing' => 'Đang xử lý',
            'shipped' => 'Đã giao',
            'delivered' => 'Đã nhận',
            'cancelled' => 'Đã hủy',
            'refunded' => 'Đã hoàn tiền',
        ];

        return $labels[$status] ?? $status;
    }
}
