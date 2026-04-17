<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Review extends Model
{
    protected $fillable = [
        'product_id', 'user_id', 'rating', 'comment', 'is_approved'
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_approved' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(ReviewReply::class)->orderBy('created_at');
    }

    /**
     * Cập nhật rating_average, rating_count, review_count trên Product.
     */
    public static function recalculateProductRating(Product $product): void
    {
        $stats = self::where('product_id', $product->id)
            ->where('is_approved', true)
            ->selectRaw('COUNT(*) as count, AVG(rating) as avg_rating')
            ->first();

        $product->update([
            'review_count' => $stats->count ?? 0,
            'rating_count' => $stats->count ?? 0,
            'rating_average' => round((float) ($stats->avg_rating ?? 0), 2),
        ]);
    }
}
