<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lens extends Model
{
    public const TYPE_LABELS = [
        'myopia' => 'Lens cận',
        'hyperopia' => 'Lens viễn',
        'blue_light' => 'Lens chống ánh sáng xanh',
        'photochromic' => 'Lens đổi màu',
        'progressive' => 'Lens đa tròng',
        'sunglasses' => 'Lens kính mát',
    ];

    protected $fillable = [
        'sku',
        'name',
        'slug',
        'description',
        'lens_type',
        'base_price',
        'stock_quantity',
        'requires_prescription',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'requires_prescription' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'lens_type_label',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getLensTypeLabelAttribute(): string
    {
        return self::TYPE_LABELS[$this->lens_type] ?? $this->lens_type;
    }
}
