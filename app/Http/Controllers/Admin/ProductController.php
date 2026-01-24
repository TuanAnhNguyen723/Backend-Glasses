<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index()
    {
        return view('admin.products');
    }

    public function getProducts(Request $request)
    {
        // Tối ưu: Chỉ load primary image thay vì tất cả images
        $query = Product::with([
            'category:id,name',
            'images' => function($q) {
                $q->where('is_primary', true)->select('id', 'product_id', 'image_url', 'is_primary')
                  ->limit(1);
            }
        ])->select([
            'id', 'name', 'sku', 'description', 'category_id', 'frame_shape',
            'base_price', 'stock_quantity', 'low_stock_threshold', 'is_active', 'created_at'
        ]);

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->has('category') && $request->category) {
            $query->where('category_id', $request->category);
        }

        // Filter by frame shape
        if ($request->has('frame_shape') && $request->frame_shape) {
            $query->where('frame_shape', $request->frame_shape);
        }

        // Filter by status
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 10);
        $products = $query->paginate($perPage);

        $products->getCollection()->transform(function($product) {
            $primaryImage = $product->images->first();
            $imageUrl = $primaryImage ? $primaryImage->image_url : 'https://via.placeholder.com/100';
            
            // Tính % tồn kho
            $stockPercentage = $product->low_stock_threshold > 0 
                ? min(100, ($product->stock_quantity / ($product->low_stock_threshold * 10)) * 100)
                : 100;

            // Xác định trạng thái tồn kho
            $stockStatus = 'good';
            $stockStatusLabel = 'Đủ hàng';
            if ($product->stock_quantity == 0) {
                $stockStatus = 'out';
                $stockStatusLabel = 'Hết hàng';
            } elseif ($product->stock_quantity <= $product->low_stock_threshold) {
                $stockStatus = 'low';
                $stockStatusLabel = 'Sắp hết';
            }

            return [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'image_url' => $imageUrl,
                'category' => $product->category ? $product->category->name : 'Chưa phân loại',
                'frame_shape' => $product->frame_shape,
                'price' => number_format($product->base_price, 0, ',', '.'),
                'stock_quantity' => $product->stock_quantity,
                'low_stock_threshold' => $product->low_stock_threshold,
                'stock_percentage' => round($stockPercentage),
                'stock_status' => $stockStatus,
                'stock_status_label' => $stockStatusLabel,
                'is_active' => $product->is_active,
                'status_label' => $product->is_active ? 'Hoạt động' : 'Đã lưu trữ',
            ];
        });

        return response()->json($products);
    }

    public function getStats()
    {
        // Tối ưu: Gộp tất cả vào 1 query
        $stats = Product::selectRaw('
            SUM(stock_quantity) as total_inventory,
            SUM(CASE WHEN stock_quantity > 0 AND stock_quantity <= low_stock_threshold THEN 1 ELSE 0 END) as low_stock_items,
            SUM(base_price * stock_quantity) as stock_value
        ')->first();

        // Tính % tăng trưởng tồn kho (so với tháng trước - giả sử)
        $inventoryGrowth = 2; // Có thể tính từ lịch sử nếu có

        return response()->json([
            'total_inventory' => $stats->total_inventory ?? 0,
            'low_stock_items' => $stats->low_stock_items ?? 0,
            'stock_value' => $stats->stock_value ?? 0,
            'inventory_growth' => $inventoryGrowth,
        ]);
    }

    public function getFilters()
    {
        // Tối ưu: Cache filters vì ít thay đổi
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->select('id', 'name')
            ->get();

        // Tối ưu: Chỉ select frame_shape thay vì load toàn bộ model
        $frameShapes = Product::select('frame_shape')
            ->whereNotNull('frame_shape')
            ->distinct()
            ->pluck('frame_shape')
            ->map(function($shape) {
                return [
                    'value' => $shape,
                    'label' => ucfirst($shape),
                ];
            });

        return response()->json([
            'categories' => $categories,
            'frame_shapes' => $frameShapes,
        ]);
    }
}
