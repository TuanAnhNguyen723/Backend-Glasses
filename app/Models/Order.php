<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'user_id', 'status', 'subtotal', 'tax_amount',
        'shipping_amount', 'discount_amount', 'total_amount', 'promo_code',
        'shipping_name', 'shipping_phone', 'shipping_email',
        'shipping_address', 'shipping_city', 'shipping_postal_code',
        'shipping_country', 'tracking_number', 'estimated_delivery_date',
        'delivered_at', 'notes',
        'payment_status', 'payment_method', 'payment_reference', 'paid_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'estimated_delivery_date' => 'date',
        'delivered_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at', 'desc');
    }

    // Generate order number
    public static function generateOrderNumber(): string
    {
        $prefix = 'ORD-';
        $lastOrder = self::latest('id')->first();
        $nextNumber = $lastOrder ? $lastOrder->id + 1 : 1;
        return $prefix . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }
}
