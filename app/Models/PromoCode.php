<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PromoCode extends Model
{
    public const SCOPE_ALL_PRODUCTS = 'all_products';
    public const SCOPE_PRODUCT = 'product';
    public const TYPE_PERCENT = 'percent';
    public const TYPE_FIXED = 'fixed';

    protected $fillable = [
        'code',
        'name',
        'scope',
        'product_id',
        'discount_type',
        'discount_value',
        'min_order_amount',
        'max_discount_amount',
        'usage_limit',
        'used_count',
        'starts_at',
        'ends_at',
        'is_active',
        'description',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public static function validScopes(): array
    {
        return [self::SCOPE_ALL_PRODUCTS, self::SCOPE_PRODUCT];
    }

    public static function validDiscountTypes(): array
    {
        return [self::TYPE_PERCENT, self::TYPE_FIXED];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function claims(): HasMany
    {
        return $this->hasMany(UserPromoCode::class);
    }

    public function isAvailable(): bool
    {
        $now = now();

        if (! $this->is_active) {
            return false;
        }
        if ($this->starts_at && $this->starts_at->gt($now)) {
            return false;
        }
        if ($this->ends_at && $this->ends_at->lt($now)) {
            return false;
        }
        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }
}
