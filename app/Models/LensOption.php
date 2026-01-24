<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LensOption extends Model
{
    protected $fillable = [
        'product_id', 'name', 'description', 'price_adjustment',
        'is_default', 'is_active', 'sort_order'
    ];

    protected $casts = [
        'price_adjustment' => 'decimal:2',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
