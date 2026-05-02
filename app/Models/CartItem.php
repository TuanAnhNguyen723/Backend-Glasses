<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = [
        'user_id', 'session_id', 'product_id', 'product_color_id',
        'lens_id', 'lens_option_id', 'prescription_type', 'prescription_data',
        'prescription_hash', 'quantity', 'unit_price'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'prescription_data' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productColor(): BelongsTo
    {
        return $this->belongsTo(ProductColor::class);
    }

    public function lensOption(): BelongsTo
    {
        return $this->belongsTo(LensOption::class);
    }

    public function lens(): BelongsTo
    {
        return $this->belongsTo(Lens::class);
    }
}
