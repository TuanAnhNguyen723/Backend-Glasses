<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Http\Resources\RelatedProductResource;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'images', 'colors', 'lensOptions'])
            ->active();

        // Filters
        if ($request->has('category')) {
            $query->inCategory($request->category);
        }

        if ($request->has('frame_shape')) {
            $query->byFrameShape($request->frame_shape);
        }

        if ($request->has('min_price')) {
            $query->where('base_price', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {
            $query->where('base_price', '<=', $request->max_price);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 12);
        $products = $query->paginate($perPage);

        return ProductResource::collection($products);
    }

    public function show($id)
    {
        $product = Product::with([
            'category',
            'images',
            'colors',
            'lensOptions',
            'primaryImage',
        ])->active()->findOrFail($id);

        return new ProductResource($product);
    }

    public function related(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|integer|exists:categories,id',
            'exclude_id' => 'nullable|integer',
            'limit' => 'nullable|integer|min:1|max:20',
        ]);

        $limit = $validated['limit'] ?? 8;

        $query = Product::with(['category', 'images', 'primaryImage'])
            ->active()
            ->where('category_id', $validated['category_id']);

        if (!empty($validated['exclude_id'])) {
            $query->where('id', '!=', $validated['exclude_id']);
        }

        $products = $query
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        return RelatedProductResource::collection($products);
    }

    public function categories(Request $request)
    {
        $includeChildren = $request->boolean('include_children', true);
        $withProducts = $request->boolean('with_products', false);

        $query = Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->withCount([
                'products as products_count' => function ($q) {
                    $q->where('is_active', true);
                }
            ]);

        // Optional: include products (only when explicitly requested)
        if ($withProducts) {
            $query->with(['products' => function ($q) {
                $q->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('created_at', 'desc')
                    ->select(['id', 'category_id', 'sku', 'name', 'slug', 'base_price', 'compare_price', 'is_active']);
            }]);
        }

        // Optional: include children categories
        if ($includeChildren) {
            $query->with(['children' => function ($q) {
                $q->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('name');
            }]);
        }

        $categories = $query->get(['id', 'name', 'slug', 'description', 'parent_id', 'sort_order', 'is_active']);

        // Return only top-level categories when including children
        if ($includeChildren) {
            $categories = $categories->whereNull('parent_id')->values();
        }

        return response()->json([
            'data' => $categories,
        ]);
    }
}
