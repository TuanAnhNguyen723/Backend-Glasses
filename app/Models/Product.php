<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'sku', 'name', 'slug', 'description', 'short_description',
        'base_price', 'compare_price', 'category_id', 'brand_id', 'frame_shape',
        'frame_type', 'lens_compatibility', 'material', 'size', 'bridge', 'stock_quantity',
        'low_stock_threshold', 'rating_average', 'rating_count',
        'review_count', 'badge', 'is_featured', 'is_active',
        'sort_order', 'meta_title', 'meta_description'
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'rating_average' => 'decimal:2',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function colors(): HasMany
    {
        return $this->hasMany(ProductColor::class)->where('is_active', true);
    }

    public function lensOptions(): HasMany
    {
        return $this->hasMany(LensOption::class)->where('is_active', true);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->where('is_approved', true);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeInCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByFrameShape($query, $shape)
    {
        return $query->where('frame_shape', $shape);
    }
}
