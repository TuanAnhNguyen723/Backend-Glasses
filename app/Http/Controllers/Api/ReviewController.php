<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Danh sách review của sản phẩm (chỉ hiển thị đã duyệt).
     */
    public function index(Request $request, $productId): JsonResponse
    {
        $product = Product::find($productId);
        if (!$product) {
            return response()->json(['message' => 'Sản phẩm không tồn tại'], 404);
        }

        $perPage = min((int) $request->get('per_page', 15), 50);
        $reviews = Review::where('product_id', $productId)
            ->where('is_approved', true)
            ->with(['user:id,name,avatar', 'replies.user:id,name'])
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json([
            'reviews' => ReviewResource::collection($reviews),
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
            ],
            'product_summary' => [
                'rating_average' => (float) $product->rating_average,
                'review_count' => (int) $product->review_count,
            ],
        ]);
    }

    /**
     * Lấy review của user hiện tại cho sản phẩm (nếu có). Dùng để hiển thị "Chỉnh sửa đánh giá".
     */
    public function myReview(Request $request, $productId): JsonResponse
    {
        $product = Product::find($productId);
        if (!$product) {
            return response()->json(['message' => 'Sản phẩm không tồn tại'], 404);
        }

        $review = Review::where('product_id', $productId)
            ->where('user_id', $request->user()->id)
            ->with(['user:id,name,avatar', 'replies.user:id,name'])
            ->first();

        return response()->json([
            'review' => $review ? new ReviewResource($review) : null,
            'can_review' => $request->user()->hasPurchasedProduct((int) $productId),
        ]);
    }

    /**
     * Tạo review (chỉ user đã mua sản phẩm).
     */
    public function store(Request $request, $productId): JsonResponse
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
        ]);

        $product = Product::find($productId);
        if (!$product) {
            return response()->json(['message' => 'Sản phẩm không tồn tại'], 404);
        }

        $user = $request->user();
        if (!$user->hasPurchasedProduct((int) $productId)) {
            return response()->json([
                'message' => 'Bạn cần mua sản phẩm này trước khi đánh giá.',
            ], 403);
        }

        $existing = Review::where('product_id', $productId)->where('user_id', $user->id)->first();
        if ($existing) {
            return response()->json([
                'message' => 'Bạn đã đánh giá sản phẩm này rồi. Bạn có thể chỉnh sửa đánh giá hiện tại.',
                'review_id' => $existing->id,
            ], 422);
        }

        $review = Review::create([
            'product_id' => $productId,
            'user_id' => $user->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'is_approved' => true, // Auto duyệt vì đã verify mua hàng
        ]);

        Review::recalculateProductRating($product);

        return response()->json([
            'message' => 'Đánh giá đã được gửi thành công.',
            'review' => new ReviewResource($review->load(['user:id,name,avatar', 'replies.user:id,name'])),
        ], 201);
    }

    /**
     * Cập nhật review (chỉ chủ sở hữu).
     */
    public function update(Request $request, $id): JsonResponse
    {
        $request->validate([
            'rating' => 'sometimes|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
        ]);

        $review = Review::find($id);
        if (!$review) {
            return response()->json(['message' => 'Đánh giá không tồn tại'], 404);
        }

        if ($review->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Bạn không có quyền sửa đánh giá này.'], 403);
        }

        if ($request->has('rating')) {
            $review->rating = $request->rating;
        }
        if (array_key_exists('comment', $request->all())) {
            $review->comment = $request->comment;
        }
        $review->save();

        Review::recalculateProductRating($review->product);

        return response()->json([
            'message' => 'Đánh giá đã được cập nhật.',
            'review' => new ReviewResource($review->load(['user:id,name,avatar', 'replies.user:id,name'])),
        ]);
    }

    /**
     * Xóa review (chỉ chủ sở hữu).
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $review = Review::find($id);
        if (!$review) {
            return response()->json(['message' => 'Đánh giá không tồn tại'], 404);
        }

        if ($review->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Bạn không có quyền xóa đánh giá này.'], 403);
        }

        $product = $review->product;
        $review->delete();
        Review::recalculateProductRating($product);

        return response()->json(['message' => 'Đánh giá đã được xóa.']);
    }
}
