<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\ProductColor;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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
        $product = Product::with(['category', 'brand', 'images', 'colors'])->findOrFail($id);
        
        // Generate fresh signed URLs for existing images
        foreach ($product->images as $image) {
            if ($image->image_path) {
                try {
                    // Generate fresh signed URL from path (valid for 1 week - max allowed)
                    $image->image_url = Storage::disk('backblaze')->temporaryUrl(
                        $image->image_path,
                        now()->addWeek() // 1 week is the maximum for AWS S3 signed URLs
                    );
                } catch (\Exception $e) {
                    // If signed URL generation fails, keep the existing URL or try to generate from image_url
                    Log::warning('Failed to generate signed URL for image: ' . $e->getMessage());
                    // If image_url exists but is expired, try to extract path and regenerate
                    if ($image->image_url && strpos($image->image_url, 'http') === 0) {
                        // Keep existing URL as fallback
                    }
                }
            } elseif ($image->image_url) {
                // If no image_path but has image_url, check if it's a valid URL
                // For old images without image_path, we'll use the stored URL
                // In the future, you may want to migrate old URLs to extract paths
            }
        }
        
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
        $brands = Brand::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
        return view('admin.products.edit', compact('product', 'categories', 'brands'));
    }

    public function show($id)
    {
        $product = Product::with(['category', 'images', 'colors'])->findOrFail($id);
        
        $primaryImage = $product->images->where('is_primary', true)->first() 
            ?? $product->images->first();
        
        // Generate signed URL from path if available, otherwise use stored URL
        $imageUrl = 'https://via.placeholder.com/100';
        if ($primaryImage) {
            if ($primaryImage->image_path) {
                try {
                    $imageUrl = Storage::disk('backblaze')->temporaryUrl(
                        $primaryImage->image_path,
                        now()->addHour()
                    );
                } catch (\Exception $e) {
                    $imageUrl = $primaryImage->image_url ?? $imageUrl;
                }
            } else {
                $imageUrl = $primaryImage->image_url ?? $imageUrl;
            }
        }
        
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
                // Generate fresh signed URL from path
                $url = $img->image_url; // Fallback to stored URL
                if ($img->image_path) {
                    try {
                        $url = Storage::disk('backblaze')->temporaryUrl(
                            $img->image_path,
                            now()->addHour()
                        );
                    } catch (\Exception $e) {
                        // Use stored URL if signed URL generation fails
                    }
                }
                
                return [
                    'id' => $img->id,
                    'url' => $url,
                    'is_primary' => $img->is_primary,
                    'product_color_id' => $img->product_color_id,
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
                'brand_id' => 'nullable|exists:brands,id',
                'frame_shape' => 'required|string',
                'frame_type' => 'nullable|string',
                'lens_compatibility' => 'nullable|string',
                'material' => 'nullable|string|max:255',
                'badge' => 'nullable|string|max:100',
                'description' => 'nullable|string',
                'is_active' => 'nullable|boolean',
                'primary_image_index' => 'nullable|integer',
                'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
                'images_meta' => 'nullable|string', // JSON array mapping images -> color_hex
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
                'brand_id' => $validated['brand_id'] ?? null,
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

        // Handle frame colors FIRST (so we can map hex -> product_color_id for images)
        $colorIdByHex = [];
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
                    if (!is_string($hexCode) || empty($hexCode)) continue;
                    $hexCode = strtoupper($hexCode);
                    $colorName = $colorNames[$hexCode] ?? $colorNames[strtolower($hexCode)] ?? 'Tùy chỉnh';

                    $color = ProductColor::create([
                        'product_id' => $product->id,
                        'name' => $colorName,
                        'hex_code' => $hexCode,
                        'price_adjustment' => 0,
                        'stock_quantity' => $product->stock_quantity, // default
                        'is_active' => true,
                        'sort_order' => $index + 1,
                    ]);

                    $colorIdByHex[strtoupper($hexCode)] = $color->id;
                }
            }
        }

        // Parse images meta (index-aligned with request->file('images'))
        $imagesMeta = [];
        if ($request->filled('images_meta')) {
            $decoded = json_decode($request->input('images_meta'), true);
            if (is_array($decoded)) $imagesMeta = $decoded;
        }

        // Handle image uploads
        if ($request->hasFile('images')) {
            $primarySet = false;
            $primaryIndex = (int)$request->input('primary_image_index', 0);
            $uploadAttempts = 0;
            $uploadFailures = [];
            $imagesCountBefore = $product->images()->count();

            foreach ($request->file('images') as $index => $image) {
                $uploadAttempts++;
                try {
                    $isPrimary = ($index == $primaryIndex) && !$primarySet;

                    // Determine color for this image (optional but recommended)
                    $meta = $imagesMeta[$index] ?? null;
                    $hex = null;
                    if (is_array($meta)) {
                        $hex = $meta['color_hex'] ?? $meta['hex_code'] ?? $meta['color'] ?? null;
                    }
                    $hex = is_string($hex) ? strtoupper(trim($hex)) : null;
                    $productColorId = $hex && isset($colorIdByHex[$hex]) ? $colorIdByHex[$hex] : null;

                    // Generate unique filename
                    $originalName = $image->getClientOriginalName();
                    $extension = $image->getClientOriginalExtension();
                    $nameWithoutExtension = pathinfo($originalName, PATHINFO_FILENAME);
                    $cleanName = Str::slug($nameWithoutExtension, '_');
                    $uniqueName = $cleanName . '_' . $product->id . '_' . time() . '_' . $index . '.' . $extension;
                    $fileName = 'products/' . $uniqueName;

                    // Upload to Backblaze B2 (Storage disk) – trả về path
                    $imagePath = $this->uploadToBackblaze($image, $fileName);

                    // Signed URL cho hiển thị (nếu lỗi vẫn lưu path, URL để sau sinh lại)
                    try {
                        $signedUrl = $this->getSignedUrl($imagePath, 10080);
                    } catch (\Throwable $e) {
                        Log::warning('getSignedUrl failed, saving path only: ' . $e->getMessage());
                        $signedUrl = '';
                    }

                    $product->images()->create([
                        'image_path' => $imagePath,
                        'image_url' => $signedUrl ?: $imagePath,
                        'product_color_id' => $productColorId,
                        'is_primary' => $isPrimary,
                        'alt_text' => $product->name,
                        'sort_order' => $index,
                    ]);

                    if ($isPrimary) {
                        $primarySet = true;
                    }
                } catch (\Exception $e) {
                    Log::error('Error uploading image to Backblaze: ' . $e->getMessage());
                    $uploadFailures[] = $e->getMessage();
                    continue;
                }
            }

            // Nếu có ảnh gửi lên nhưng không có ảnh nào lưu được → trả lỗi rõ
            $imagesCountAfter = $product->images()->count();
            if ($uploadAttempts > 0 && $imagesCountAfter === $imagesCountBefore) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể tải ảnh lên Backblaze. Kiểm tra cấu hình .env: AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY, AWS_BUCKET, AWS_ENDPOINT.',
                    'errors' => ['images' => [$uploadFailures[0] ?? 'Upload thất bại']],
                ], 422);
            }

            // Ensure at least one primary image
            if (!$primarySet) {
                $firstImage = $product->images()->orderBy('sort_order')->first();
                if ($firstImage instanceof \App\Models\ProductImage) {
                    $firstImage->update(['is_primary' => true]);
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
        $query = Product::with(['category', 'brand', 'images']);

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

        // Filter by brand
        if ($request->has('brand') && $request->brand) {
            $query->where('brand_id', $request->brand);
        }

        // Filter by price range
        if ($request->has('price') && $request->price) {
            $priceRange = explode('-', $request->price);
            if (count($priceRange) === 2) {
                $minPrice = (int)$priceRange[0];
                $maxPrice = (int)$priceRange[1];
                $query->whereBetween('base_price', [$minPrice, $maxPrice]);
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
            
            // Generate signed URL from path if available
            $imageUrl = 'https://via.placeholder.com/100';
            if ($primaryImage) {
                if ($primaryImage->image_path) {
                    try {
                        // Use Storage facade to generate signed URL
                        $imageUrl = Storage::disk('backblaze')->temporaryUrl(
                            $primaryImage->image_path,
                            now()->addHour()
                        );
                    } catch (\Exception $e) {
                        $imageUrl = $primaryImage->image_url ?? $imageUrl;
                    }
                } else {
                    $imageUrl = $primaryImage->image_url ?? $imageUrl;
                }
            }
            
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
                'brand' => $product->brand ? $product->brand->name : null,
                'frame_shape' => $product->frame_shape,
                'frame_type' => $product->frame_type,
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
        
        $brands = Brand::where('is_active', true)
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
            'brands' => $brands,
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
                'brand_id' => 'nullable|exists:brands,id',
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
                'images_meta' => 'nullable|string', // JSON array mapping NEW images -> color_hex (index aligned)
                'existing_images_meta' => 'nullable|string', // JSON array mapping existing image id -> color_hex
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
                'brand_id' => $validated['brand_id'] ?? null,
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
                        // Delete file from Backblaze
                        try {
                            // Use image_path if available, otherwise try to extract from image_url
                            $path = $image->image_path;
                            
                            if (empty($path)) {
                                // Fallback: try to extract path from URL
                                $imageUrl = $image->image_url;
                                if (strpos($imageUrl, 'http') === 0) {
                                    $parsedUrl = parse_url($imageUrl);
                                    $path = ltrim($parsedUrl['path'] ?? '', '/');
                                    
                                    // Remove bucket name from path if present
                                    $bucketName = env('AWS_BUCKET');
                                    if (strpos($path, $bucketName . '/') === 0) {
                                        $path = substr($path, strlen($bucketName) + 1);
                                    }
                                } else {
                                    $path = $imageUrl;
                                }
                            }
                            
                            // Delete from Backblaze
                            if (!empty($path)) {
                                Storage::disk('backblaze')->delete($path);
                            }
                        } catch (\Exception $e) {
                            // Log error but continue deletion
                            Log::warning('Failed to delete image from Backblaze: ' . $e->getMessage());
                        }
                        $image->delete();
                    }
                }
            }
        }

        // Handle frame colors update (do this BEFORE image uploads so we can map hex -> id)
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

        // Handle new image uploads
        if ($request->hasFile('images')) {
            // Build color map from current colors (after potential update below)
            $colorIdByHex = $product->colors()->pluck('id', 'hex_code')->mapWithKeys(function ($id, $hex) {
                return [strtoupper($hex) => $id];
            })->toArray();

            // Parse meta for new images (index aligned with $request->file('images'))
            $imagesMeta = [];
            if ($request->filled('images_meta')) {
                $decoded = json_decode($request->input('images_meta'), true);
                if (is_array($decoded)) $imagesMeta = $decoded;
            }

            $primarySet = false;
            $primaryIndex = (int)$request->input('primary_image_index', 0);
            $existingImagesCount = $product->images()->count();
            $uploadAttempts = 0;
            $uploadFailures = [];

            foreach ($request->file('images') as $index => $image) {
                $uploadAttempts++;
                try {
                    $isPrimary = ($index == $primaryIndex) && !$primarySet;

                    $meta = $imagesMeta[$index] ?? null;
                    $hex = null;
                    if (is_array($meta)) {
                        $hex = $meta['color_hex'] ?? $meta['hex_code'] ?? $meta['color'] ?? null;
                    }
                    $hex = is_string($hex) ? strtoupper(trim($hex)) : null;
                    $productColorId = $hex && isset($colorIdByHex[$hex]) ? $colorIdByHex[$hex] : null;

                    // Generate unique filename
                    $originalName = $image->getClientOriginalName();
                    $extension = $image->getClientOriginalExtension();
                    $nameWithoutExtension = pathinfo($originalName, PATHINFO_FILENAME);
                    $cleanName = Str::slug($nameWithoutExtension, '_');
                    $uniqueName = $cleanName . '_' . $product->id . '_' . time() . '_' . ($existingImagesCount + $index) . '.' . $extension;
                    $fileName = 'products/' . $uniqueName;

                    // Upload to Backblaze B2 (Storage disk)
                    $imagePath = $this->uploadToBackblaze($image, $fileName);

                    try {
                        $signedUrl = $this->getSignedUrl($imagePath, 10080);
                    } catch (\Throwable $e) {
                        Log::warning('getSignedUrl failed, saving path only: ' . $e->getMessage());
                        $signedUrl = '';
                    }

                    $newImage = $product->images()->create([
                        'image_path' => $imagePath,
                        'image_url' => $signedUrl ?: $imagePath,
                        'product_color_id' => $productColorId,
                        'is_primary' => $isPrimary,
                        'alt_text' => $product->name,
                        'sort_order' => $existingImagesCount + $index,
                    ]);

                    if ($isPrimary) {
                        $product->images()->where('id', '!=', $newImage->id)
                            ->update(['is_primary' => false]);
                        $primarySet = true;
                    }
                } catch (\Exception $e) {
                    Log::error('Error uploading image to Backblaze: ' . $e->getMessage());
                    $uploadFailures[] = $e->getMessage();
                    continue;
                }
            }

            if ($uploadAttempts > 0 && count($uploadFailures) === $uploadAttempts) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể tải ảnh lên Backblaze. Kiểm tra .env: AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY, AWS_BUCKET, AWS_ENDPOINT.',
                    'errors' => ['images' => [$uploadFailures[0] ?? 'Upload thất bại']],
                ], 422);
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

        // Update existing image -> color mapping (after colors update)
        if ($request->filled('existing_images_meta')) {
            $decoded = json_decode($request->input('existing_images_meta'), true);
            if (is_array($decoded)) {
                $colorIdByHex = $product->colors()->pluck('id', 'hex_code')->mapWithKeys(function ($id, $hex) {
                    return [strtoupper($hex) => $id];
                })->toArray();

                foreach ($decoded as $row) {
                    if (!is_array($row)) continue;
                    $imageId = $row['id'] ?? null;
                    $hex = $row['color_hex'] ?? $row['hex_code'] ?? $row['color'] ?? null;
                    $hex = is_string($hex) ? strtoupper(trim($hex)) : null;
                    $productColorId = $hex && isset($colorIdByHex[$hex]) ? $colorIdByHex[$hex] : null;

                    if (!$imageId) continue;
                    $img = ProductImage::where('product_id', $product->id)->where('id', $imageId)->first();
                    if ($img) {
                        $img->update(['product_color_id' => $productColorId]);
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Sản phẩm đã được cập nhật thành công',
            'product' => $product->load('category', 'images', 'colors'),
        ], 200);
    }

    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);
            
            // Xóa tất cả hình ảnh của sản phẩm từ Backblaze
            foreach ($product->images as $image) {
                try {
                    // Use image_path if available, otherwise try to extract from image_url
                    $path = $image->image_path;
                    
                    if (empty($path)) {
                        // Fallback: try to extract path from URL
                        $imageUrl = $image->image_url;
                        if (strpos($imageUrl, 'http') === 0) {
                            $parsedUrl = parse_url($imageUrl);
                            $path = ltrim($parsedUrl['path'] ?? '', '/');
                            
                            // Remove bucket name from path if present
                            $bucketName = env('AWS_BUCKET');
                            if (strpos($path, $bucketName . '/') === 0) {
                                $path = substr($path, strlen($bucketName) + 1);
                            }
                        } else {
                            $path = $imageUrl;
                        }
                    }
                    
                    // Delete from Backblaze
                    if (!empty($path)) {
                        Storage::disk('backblaze')->delete($path);
                    }
                } catch (\Exception $e) {
                    // Log error but continue deletion
                    Log::warning('Failed to delete image from Backblaze: ' . $e->getMessage());
                }
                $image->delete();
            }
            
            // Xóa màu sắc
            $product->colors()->delete();
            
            // Xóa sản phẩm
            $product->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Sản phẩm đã được xóa thành công',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xóa sản phẩm: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper method to upload image to Backblaze B2 (dùng Laravel Storage disk để đồng bộ config với temporaryUrl).
     *
     * @param \Illuminate\Http\UploadedFile $image
     * @param string $fileName
     * @return string The path of the uploaded image (for private buckets, use signed URL when displaying)
     * @throws \Exception
     */
    private function uploadToBackblaze($image, $fileName)
    {
        $contents = file_get_contents($image->getRealPath());
        $stored = Storage::disk('backblaze')->put($fileName, $contents);

        if ($stored === false) {
            throw new \RuntimeException('Storage::put returned false for path: ' . $fileName);
        }

        return $fileName;
    }

    /**
     * Get signed URL for private bucket image
     * 
     * @param string $path The path stored in database
     * @param int $expirationMinutes Expiration time in minutes (default: 60 minutes, max: 10080 = 1 week)
     * @return string Signed URL
     */
    private function getSignedUrl($path, $expirationMinutes = 60)
    {
        try {
            // AWS S3 signed URLs have a maximum expiration of 1 week (10080 minutes)
            $maxExpiration = 10080; // 1 week
            $expirationMinutes = min($expirationMinutes, $maxExpiration);
            
            // Generate signed URL using Storage facade
            return Storage::disk('backblaze')->temporaryUrl(
                $path,
                now()->addMinutes($expirationMinutes)
            );
        } catch (\Exception $e) {
            Log::error('Failed to generate signed URL: ' . $e->getMessage());
            // Fallback to regular URL if signed URL fails
            $endpoint = env('AWS_ENDPOINT', 'https://s3.us-east-005.backblazeb2.com');
            $bucket = env('AWS_BUCKET', 'Glasses');
            return rtrim($endpoint, '/') . '/' . $bucket . '/' . $path;
        }
    }
}
