<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductColor;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public function index()
    {
        return view('admin.products');
    }

    public function create()
    {
        return view('admin.products.create');
    }

    public function edit($id)
    {
        $product = Product::with(['category', 'images', 'colors'])->findOrFail($id);
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function show($id)
    {
        $product = Product::with(['category', 'images', 'colors'])->findOrFail($id);
        
        $primaryImage = $product->images->where('is_primary', true)->first() 
            ?? $product->images->first();
        $imageUrl = $primaryImage ? $primaryImage->image_url : 'https://via.placeholder.com/100';
        
        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'base_price' => $product->base_price,
            'stock_quantity' => $product->stock_quantity,
            'low_stock_threshold' => $product->low_stock_threshold,
            'category_id' => $product->category_id,
            'frame_shape' => $product->frame_shape,
            'frame_type' => $product->frame_type ?? '',
            'lens_compatibility' => $product->lens_compatibility ?? '',
            'material' => $product->material,
            'badge' => $product->badge,
            'description' => $product->description,
            'is_active' => $product->is_active,
            'image_url' => $imageUrl,
            'images' => $product->images->map(function($img) {
                return [
                    'id' => $img->id,
                    'url' => $img->image_url,
                    'is_primary' => $img->is_primary,
                ];
            }),
            'colors' => $product->colors->pluck('hex_code')->toArray(),
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'sku' => 'required|string|max:100|unique:products,sku',
                'base_price' => 'required|numeric|min:0',
                'stock_quantity' => 'required|integer|min:0',
                'low_stock_threshold' => 'nullable|integer|min:0',
                'category_id' => 'required|exists:categories,id',
                'frame_shape' => 'required|string',
                'frame_type' => 'nullable|string',
                'lens_compatibility' => 'nullable|string',
                'material' => 'nullable|string|max:255',
                'badge' => 'nullable|string|max:100',
                'description' => 'nullable|string',
                'is_active' => 'nullable|boolean',
                'primary_image_index' => 'nullable|integer',
                'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
            ], [
                'name.required' => 'Tên sản phẩm là bắt buộc',
                'sku.required' => 'SKU là bắt buộc',
                'sku.unique' => 'SKU đã tồn tại',
                'base_price.required' => 'Giá là bắt buộc',
                'stock_quantity.required' => 'Tồn kho là bắt buộc',
                'category_id.required' => 'Danh mục là bắt buộc',
                'frame_shape.required' => 'Hình dạng khung là bắt buộc',
                'images.*.image' => 'File phải là hình ảnh',
                'images.*.max' => 'Kích thước file không được vượt quá 5MB',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors(),
            ], 422);
        }

        try {
            // Generate slug from name
            $slug = Str::slug($validated['name']);
            $originalSlug = $slug;
            $counter = 1;
            
            // Ensure slug is unique
            while (Product::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
            
            $product = Product::create([
                'name' => $validated['name'],
                'slug' => $slug,
                'sku' => $validated['sku'],
                'base_price' => $validated['base_price'],
                'stock_quantity' => $validated['stock_quantity'],
                'low_stock_threshold' => $validated['low_stock_threshold'] ?? 10,
                'category_id' => $validated['category_id'],
                'frame_shape' => $validated['frame_shape'],
                'material' => $validated['material'] ?? null,
                'badge' => $validated['badge'] ?? null,
                'description' => $validated['description'] ?? '',
                'is_active' => $request->input('is_active', '1') == '1',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tạo sản phẩm: ' . $e->getMessage(),
            ], 500);
        }

        // Handle image uploads
        if ($request->hasFile('images')) {
            $primarySet = false;
            $primaryIndex = (int)$request->input('primary_image_index', 0);
            
            // Create directory if not exists
            $uploadDir = public_path('uploads/products');
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            foreach ($request->file('images') as $index => $image) {
                $isPrimary = ($index == $primaryIndex) && !$primarySet;
                
                // Generate unique filename
                $extension = $image->getClientOriginalExtension();
                $filename = $product->id . '_' . time() . '_' . $index . '.' . $extension;
                $filePath = $uploadDir . '/' . $filename;
                
                // Move uploaded file
                $image->move($uploadDir, $filename);
                
                $product->images()->create([
                    'image_url' => '/uploads/products/' . $filename,
                    'is_primary' => $isPrimary,
                    'alt_text' => $product->name,
                    'sort_order' => $index,
                ]);
                
                if ($isPrimary) {
                    $primarySet = true;
                }
            }
            
            // Ensure at least one primary image
            if (!$primarySet) {
                $firstImage = $product->images()->orderBy('sort_order')->first();
                if ($firstImage instanceof \App\Models\ProductImage) {
                    $firstImage->update(['is_primary' => true]);
                }
            }
        }

        // Handle frame colors
        if ($request->has('frame_colors')) {
            $frameColors = json_decode($request->input('frame_colors'), true);
            if (is_array($frameColors)) {
                $colorNames = [
                    '#1e293b' => 'Đen',
                    '#92400e' => 'Nâu',
                    '#94a3b8' => 'Xám',
                    '#1e3a8a' => 'Xanh Dương',
                    '#fecdd3' => 'Hồng',
                ];
                
                foreach ($frameColors as $index => $hexCode) {
                    $colorName = $colorNames[$hexCode] ?? 'Tùy chỉnh';
                    ProductColor::create([
                        'product_id' => $product->id,
                        'name' => $colorName,
                        'hex_code' => $hexCode,
                        'price_adjustment' => 0,
                        'stock_quantity' => $product->stock_quantity, // Use product stock as default
                        'is_active' => true,
                        'sort_order' => $index + 1,
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Sản phẩm đã được tạo thành công',
            'product' => $product->load('category', 'images', 'colors'),
        ], 201);
    }

    public function getProducts(Request $request)
    {
        $query = Product::with(['category', 'images']);

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
            $primaryImage = $product->images->where('is_primary', true)->first() 
                ?? $product->images->first();
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
                'material' => $product->material,
                'badge' => $product->badge,
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
        // Tổng tồn kho
        $totalInventory = Product::sum('stock_quantity');

        // Số sản phẩm sắp hết hàng
        $lowStockItems = Product::whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->where('stock_quantity', '>', 0)
            ->count();

        // Giá trị tồn kho
        $stockValue = Product::selectRaw('SUM(base_price * stock_quantity) as total')
            ->first()
            ->total ?? 0;

        // Tính % tăng trưởng tồn kho (so với tháng trước - giả sử)
        $inventoryGrowth = 2; // Có thể tính từ lịch sử nếu có

        return response()->json([
            'total_inventory' => $totalInventory,
            'low_stock_items' => $lowStockItems,
            'stock_value' => $stockValue,
            'inventory_growth' => $inventoryGrowth,
        ]);
    }

    public function getFilters()
    {
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $frameShapes = Product::distinct()
            ->whereNotNull('frame_shape')
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

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'sku' => 'required|string|max:100|unique:products,sku,' . $id,
                'base_price' => 'required|numeric|min:0',
                'stock_quantity' => 'required|integer|min:0',
                'low_stock_threshold' => 'nullable|integer|min:0',
                'category_id' => 'required|exists:categories,id',
                'frame_shape' => 'required|string',
                'frame_type' => 'nullable|string',
                'lens_compatibility' => 'nullable|string',
                'material' => 'nullable|string|max:255',
                'badge' => 'nullable|string|max:100',
                'description' => 'nullable|string',
                'is_active' => 'nullable|boolean',
                'primary_image_index' => 'nullable|integer',
                'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
                'deleted_images' => 'nullable|string', // JSON array of image IDs to delete
            ], [
                'name.required' => 'Tên sản phẩm là bắt buộc',
                'sku.required' => 'SKU là bắt buộc',
                'sku.unique' => 'SKU đã tồn tại',
                'base_price.required' => 'Giá là bắt buộc',
                'stock_quantity.required' => 'Tồn kho là bắt buộc',
                'category_id.required' => 'Danh mục là bắt buộc',
                'frame_shape.required' => 'Hình dạng khung là bắt buộc',
                'images.*.image' => 'File phải là hình ảnh',
                'images.*.max' => 'Kích thước file không được vượt quá 5MB',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors(),
            ], 422);
        }

        try {
            // Generate slug from name if name changed
            $slug = $product->slug;
            if ($product->name !== $validated['name']) {
                $slug = Str::slug($validated['name']);
                $originalSlug = $slug;
                $counter = 1;
                
                while (Product::where('slug', $slug)->where('id', '!=', $id)->exists()) {
                    $slug = $originalSlug . '-' . $counter;
                    $counter++;
                }
            }
            
            // Update product
            $updateData = [
                'name' => $validated['name'],
                'slug' => $slug,
                'sku' => $validated['sku'],
                'base_price' => $validated['base_price'],
                'stock_quantity' => $validated['stock_quantity'],
                'low_stock_threshold' => $validated['low_stock_threshold'] ?? 10,
                'category_id' => $validated['category_id'],
                'frame_shape' => $validated['frame_shape'],
                'material' => $validated['material'] ?? null,
                'badge' => $validated['badge'] ?? null,
                'description' => $validated['description'] ?? '',
                'is_active' => $request->input('is_active', '1') == '1',
            ];
            
            // Add frame_type and lens_compatibility if provided
            if ($request->has('frame_type')) {
                $updateData['frame_type'] = $validated['frame_type'] ?? null;
            }
            if ($request->has('lens_compatibility')) {
                $updateData['lens_compatibility'] = $validated['lens_compatibility'] ?? null;
            }
            
            $product->update($updateData);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật sản phẩm: ' . $e->getMessage(),
            ], 500);
        }

        // Handle deleted images
        if ($request->has('deleted_images')) {
            $deletedImageIds = json_decode($request->input('deleted_images'), true);
            if (is_array($deletedImageIds)) {
                foreach ($deletedImageIds as $imageId) {
                    $image = ProductImage::find($imageId);
                    if ($image && $image->product_id == $product->id) {
                        // Delete file from storage
                        $filePath = public_path($image->image_url);
                        if (file_exists($filePath)) {
                            unlink($filePath);
                        }
                        $image->delete();
                    }
                }
            }
        }

        // Handle new image uploads
        if ($request->hasFile('images')) {
            $primarySet = false;
            $primaryIndex = (int)$request->input('primary_image_index', 0);
            $existingImagesCount = $product->images()->count();
            
            // Create directory if not exists
            $uploadDir = public_path('uploads/products');
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            foreach ($request->file('images') as $index => $image) {
                $isPrimary = ($index == $primaryIndex) && !$primarySet;
                
                // Generate unique filename
                $extension = $image->getClientOriginalExtension();
                $filename = $product->id . '_' . time() . '_' . ($existingImagesCount + $index) . '.' . $extension;
                $filePath = $uploadDir . '/' . $filename;
                
                // Move uploaded file
                $image->move($uploadDir, $filename);
                
                $product->images()->create([
                    'image_url' => '/uploads/products/' . $filename,
                    'is_primary' => $isPrimary,
                    'alt_text' => $product->name,
                    'sort_order' => $existingImagesCount + $index,
                ]);
                
                if ($isPrimary) {
                    // Unset other primary images
                    $product->images()->where('id', '!=', $product->images()->latest()->first()->id)
                        ->update(['is_primary' => false]);
                    $primarySet = true;
                }
            }
        }

        // Handle primary image change
        if ($request->has('primary_image_index') && !$request->hasFile('images')) {
            $primaryIndex = (int)$request->input('primary_image_index');
            $images = $product->images()->orderBy('sort_order')->get();
            if (isset($images[$primaryIndex])) {
                $product->images()->update(['is_primary' => false]);
                $images[$primaryIndex]->update(['is_primary' => true]);
            }
        }

        // Handle frame colors update
        if ($request->has('frame_colors')) {
            // Delete existing colors
            $product->colors()->delete();
            
            $frameColors = json_decode($request->input('frame_colors'), true);
            if (is_array($frameColors)) {
                $colorNames = [
                    '#1e293b' => 'Đen',
                    '#92400e' => 'Nâu',
                    '#94a3b8' => 'Xám',
                    '#1e3a8a' => 'Xanh Dương',
                    '#fecdd3' => 'Hồng',
                ];
                
                foreach ($frameColors as $index => $hexCode) {
                    $colorName = $colorNames[$hexCode] ?? 'Tùy chỉnh';
                    ProductColor::create([
                        'product_id' => $product->id,
                        'name' => $colorName,
                        'hex_code' => $hexCode,
                        'price_adjustment' => 0,
                        'stock_quantity' => $product->stock_quantity,
                        'is_active' => true,
                        'sort_order' => $index + 1,
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Sản phẩm đã được cập nhật thành công',
            'product' => $product->load('category', 'images', 'colors'),
        ], 200);
    }
}
