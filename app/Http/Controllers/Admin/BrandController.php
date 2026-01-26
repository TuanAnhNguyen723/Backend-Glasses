<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    public function index()
    {
        return view('admin.brands.index');
    }

    public function getBrands(Request $request)
    {
        $query = Brand::query();

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        // Filter by active status
        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->status === 'active');
        }

        // Get total count before pagination
        $total = $query->count();

        // Pagination
        $perPage = $request->get('per_page', 8);
        $page = $request->get('page', 1);
        $brands = $query->orderBy('sort_order')
                       ->orderBy('name')
                       ->skip(($page - 1) * $perPage)
                       ->take($perPage)
                       ->get();

        // Get product counts for each brand
        $brands = $brands->map(function($brand) {
            $brand->product_count = $brand->products()->count();
            return $brand;
        });

        return response()->json([
            'data' => $brands,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage),
            'from' => (($page - 1) * $perPage) + 1,
            'to' => min($page * $perPage, $total),
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'logo_url' => 'nullable|url',
                'website_url' => 'nullable|url',
                'is_active' => 'nullable|boolean',
            ], [
                'name.required' => 'Tên thương hiệu là bắt buộc',
                'logo_url.url' => 'Logo URL không hợp lệ',
                'website_url.url' => 'Website URL không hợp lệ',
            ]);

            // Generate slug
            $slug = Str::slug($validated['name']);
            $originalSlug = $slug;
            $counter = 1;
            
            while (Brand::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            $brand = Brand::create([
                'name' => $validated['name'],
                'slug' => $slug,
                'description' => $validated['description'] ?? null,
                'logo_url' => $validated['logo_url'] ?? null,
                'website_url' => $validated['website_url'] ?? null,
                'is_active' => $request->input('is_active', true),
                'sort_order' => Brand::max('sort_order') + 1,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Thương hiệu đã được tạo thành công',
                'brand' => $brand,
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tạo thương hiệu: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $brand = Brand::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'logo_url' => 'nullable|url',
                'website_url' => 'nullable|url',
                'is_active' => 'nullable|boolean',
            ], [
                'name.required' => 'Tên thương hiệu là bắt buộc',
                'logo_url.url' => 'Logo URL không hợp lệ',
                'website_url.url' => 'Website URL không hợp lệ',
            ]);

            // Generate slug if name changed
            $slug = $brand->slug;
            if ($brand->name !== $validated['name']) {
                $slug = Str::slug($validated['name']);
                $originalSlug = $slug;
                $counter = 1;
                
                while (Brand::where('slug', $slug)->where('id', '!=', $id)->exists()) {
                    $slug = $originalSlug . '-' . $counter;
                    $counter++;
                }
            }

            $brand->update([
                'name' => $validated['name'],
                'slug' => $slug,
                'description' => $validated['description'] ?? null,
                'logo_url' => $validated['logo_url'] ?? null,
                'website_url' => $validated['website_url'] ?? null,
                'is_active' => $request->input('is_active', $brand->is_active),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Thương hiệu đã được cập nhật thành công',
                'brand' => $brand,
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật thương hiệu: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatus(Request $request, $id)
    {
        try {
            $brand = Brand::findOrFail($id);
            
            // Nếu có is_active trong request thì dùng giá trị đó, không thì toggle
            if ($request->has('is_active')) {
                $brand->is_active = $request->boolean('is_active');
            } else {
                $brand->is_active = !$brand->is_active;
            }
            
            $brand->save();

            return response()->json([
                'success' => true,
                'message' => 'Trạng thái đã được cập nhật',
                'brand' => $brand,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật trạng thái: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $brand = Brand::findOrFail($id);
            
            // Check if brand has products
            if ($brand->products()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa thương hiệu vì còn sản phẩm liên quan',
                ], 400);
            }
            
            $brand->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Thương hiệu đã được xóa thành công',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xóa thương hiệu: ' . $e->getMessage(),
            ], 500);
        }
    }
}
