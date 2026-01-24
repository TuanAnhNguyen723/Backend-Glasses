<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
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
            'reviews.user'
        ])->active()->findOrFail($id);

        return new ProductResource($product);
    }

    public function categories()
    {
        return Category::with('products')->get();
    }
}
