<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\ReviewReply;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index()
    {
        return view('admin.reviews.index');
    }

    public function getReviews(Request $request): JsonResponse
    {
        $query = Review::query()
            ->with(['user:id,name,email', 'product:id,name', 'replies.user:id,name'])
            ->orderByDesc('created_at');

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('comment', 'like', '%' . $search . '%')
                    ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', '%' . $search . '%'))
                    ->orWhereHas('product', fn ($pq) => $pq->where('name', 'like', '%' . $search . '%'));
            });
        }

        if ($request->filled('status')) {
            $status = (string) $request->input('status');
            if ($status === 'approved') {
                $query->where('is_approved', true);
            } elseif ($status === 'pending') {
                $query->where('is_approved', false);
            }
        }

        if ($request->filled('rating')) {
            $rating = (int) $request->input('rating');
            if ($rating >= 1 && $rating <= 5) {
                $query->where('rating', $rating);
            }
        }

        if ($request->filled('reply_status')) {
            $replyStatus = (string) $request->input('reply_status');
            if ($replyStatus === 'replied') {
                $query->whereHas('replies', fn ($q) => $q->where('is_active', true));
            } elseif ($replyStatus === 'unreplied') {
                $query->whereDoesntHave('replies', fn ($q) => $q->where('is_active', true));
            }
        }

        $reviews = $query->paginate((int) $request->input('per_page', 10));

        $reviews->getCollection()->transform(function (Review $review) {
            $activeReplies = $review->replies->where('is_active', true)->values();

            return [
                'id' => $review->id,
                'comment' => $review->comment,
                'rating' => (int) $review->rating,
                'is_approved' => (bool) $review->is_approved,
                'created_at' => $review->created_at?->toIso8601String(),
                'user' => [
                    'id' => $review->user?->id,
                    'name' => $review->user?->name,
                    'email' => $review->user?->email,
                ],
                'product' => [
                    'id' => $review->product?->id,
                    'name' => $review->product?->name,
                ],
                'reply_count' => $activeReplies->count(),
                'latest_reply' => $activeReplies->last()
                    ? [
                        'id' => $activeReplies->last()->id,
                        'message' => $activeReplies->last()->message,
                        'replied_by' => $activeReplies->last()->user?->name ?? 'Shop',
                        'created_at' => $activeReplies->last()->created_at?->toIso8601String(),
                    ]
                    : null,
            ];
        });

        return response()->json($reviews);
    }

    public function reply(Request $request, $reviewId): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
        ], [
            'message.required' => 'Vui lòng nhập nội dung phản hồi.',
        ]);

        $review = Review::findOrFail($reviewId);

        $reply = ReviewReply::create([
            'review_id' => $review->id,
            'user_id' => null,
            'message' => trim($validated['message']),
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã gửi phản hồi bình luận.',
            'data' => [
                'id' => $reply->id,
                'review_id' => $review->id,
                'message' => $reply->message,
                'replied_by' => 'Shop',
                'created_at' => $reply->created_at?->toIso8601String(),
            ],
        ]);
    }

    public function updateReply(Request $request, $replyId): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'nullable|string|max:2000',
        ]);

        $reply = ReviewReply::findOrFail($replyId);
        $message = trim((string) ($validated['message'] ?? ''));

        // Nếu message rỗng khi chỉnh sửa => xem như xóa phản hồi (quay về chưa phản hồi)
        if ($message === '') {
            $reply->is_active = false;
            $reply->message = '';
            $reply->save();

            return response()->json([
                'success' => true,
                'message' => 'Đã xóa phản hồi. Bình luận quay về trạng thái chưa phản hồi.',
                'data' => [
                    'id' => $reply->id,
                    'review_id' => $reply->review_id,
                    'message' => $reply->message,
                    'is_active' => false,
                    'updated_at' => $reply->updated_at?->toIso8601String(),
                ],
            ]);
        }

        $reply->message = $message;
        $reply->is_active = true;
        $reply->save();

        return response()->json([
            'success' => true,
            'message' => 'Đã cập nhật phản hồi.',
            'data' => [
                'id' => $reply->id,
                'review_id' => $reply->review_id,
                'message' => $reply->message,
                'updated_at' => $reply->updated_at?->toIso8601String(),
            ],
        ]);
    }

    public function toggleApproval(Request $request, $reviewId): JsonResponse
    {
        $validated = $request->validate([
            'is_approved' => 'required|boolean',
        ]);

        $review = Review::findOrFail($reviewId);
        $review->is_approved = (bool) $validated['is_approved'];
        $review->save();

        Review::recalculateProductRating($review->product);

        return response()->json([
            'success' => true,
            'message' => $review->is_approved ? 'Đã duyệt bình luận.' : 'Đã ẩn bình luận.',
        ]);
    }
}
