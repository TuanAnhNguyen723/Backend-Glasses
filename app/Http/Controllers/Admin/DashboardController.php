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
        $now = Carbon::now();
        $lastMonth = $now->copy()->subMonth();
        $thirtyDaysAgo = $now->copy()->subDays(30);

        // Tối ưu: Gộp các query liên quan đến orders
        $ordersStats = DB::table('orders')
            ->selectRaw('
                COUNT(*) as total_orders,
                SUM(CASE WHEN status = "delivered" THEN total_amount ELSE 0 END) as total_revenue,
                SUM(CASE WHEN YEAR(created_at) = ? AND MONTH(created_at) = ? THEN 1 ELSE 0 END) as last_month_orders,
                SUM(CASE WHEN status = "delivered" AND YEAR(created_at) = ? AND MONTH(created_at) = ? THEN total_amount ELSE 0 END) as last_month_revenue
            ', [$lastMonth->year, $lastMonth->month, $lastMonth->year, $lastMonth->month])
            ->first();

        // Tối ưu: Query khách hàng hoạt động với subquery
        $activeCustomers = DB::table('users')
            ->whereExists(function($query) use ($thirtyDaysAgo) {
                $query->select(DB::raw(1))
                    ->from('orders')
                    ->whereColumn('orders.user_id', 'users.id')
                    ->where('orders.created_at', '>=', $thirtyDaysAgo);
            })
            ->count();

        $lastMonthCustomers = DB::table('users')
            ->whereExists(function($query) use ($lastMonth) {
                $query->select(DB::raw(1))
                    ->from('orders')
                    ->whereColumn('orders.user_id', 'users.id')
                    ->whereYear('orders.created_at', $lastMonth->year)
                    ->whereMonth('orders.created_at', $lastMonth->month);
            })
            ->count();

        // Tối ưu: Query sản phẩm đã bán với 1 query
        $productsStats = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', '!=', 'cancelled')
            ->selectRaw('
                SUM(order_items.quantity) as total_products_sold,
                SUM(CASE WHEN YEAR(orders.created_at) = ? AND MONTH(orders.created_at) = ? THEN order_items.quantity ELSE 0 END) as last_month_products_sold
            ', [$lastMonth->year, $lastMonth->month])
            ->first();

        // Tính toán growth
        $totalRevenue = $ordersStats->total_revenue ?? 0;
        $lastMonthRevenue = $ordersStats->last_month_revenue ?? 0;
        $revenueGrowth = $lastMonthRevenue > 0 
            ? round((($totalRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
            : 0;

        $totalOrders = $ordersStats->total_orders ?? 0;
        $lastMonthOrders = $ordersStats->last_month_orders ?? 0;
        $ordersGrowth = $lastMonthOrders > 0 
            ? round((($totalOrders - $lastMonthOrders) / $lastMonthOrders) * 100, 1)
            : 0;

        $customersGrowth = $lastMonthCustomers > 0 
            ? round((($activeCustomers - $lastMonthCustomers) / $lastMonthCustomers) * 100, 1)
            : 0;

        $productsSold = $productsStats->total_products_sold ?? 0;
        $lastMonthProductsSold = $productsStats->last_month_products_sold ?? 0;
        $productsSoldGrowth = $lastMonthProductsSold > 0 
            ? round((($productsSold - $lastMonthProductsSold) / $lastMonthProductsSold) * 100, 1)
            : 0;

        return response()->json([
            'total_revenue' => $totalRevenue,
            'total_orders' => $totalOrders,
            'active_customers' => $activeCustomers,
            'products_sold' => $productsSold,
            'revenue_growth' => $revenueGrowth,
            'orders_growth' => $ordersGrowth,
            'customers_growth' => $customersGrowth,
            'products_sold_growth' => $productsSoldGrowth,
        ]);
    }

    public function getSalesOverview()
    {
        // Tối ưu: Lấy tất cả dữ liệu trong 1 query thay vì 6 queries riêng lẻ
        $sixMonthsAgo = Carbon::now()->subMonths(5)->startOfMonth();
        
        $salesData = Order::where('status', 'delivered')
            ->where('created_at', '>=', $sixMonthsAgo)
            ->selectRaw('
                DATE_FORMAT(created_at, "%b") as month_name,
                YEAR(created_at) as year,
                MONTH(created_at) as month,
                SUM(total_amount) as revenue
            ')
            ->groupBy('year', 'month', 'month_name')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Đảm bảo có đủ 6 tháng (fill missing months với 0)
        $months = [];
        $revenues = [];
        $dataMap = $salesData->keyBy(function($item) {
            return Carbon::create($item->year, $item->month, 1)->format('Y-m');
        });

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthKey = $month->format('Y-m');
            $months[] = $month->format('M');
            $revenues[] = $dataMap->has($monthKey) ? (float)$dataMap[$monthKey]->revenue : 0;
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
                    'amount' => number_format((float)$order->total_amount, 0, ',', '.'),
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
