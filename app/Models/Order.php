<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    /** Trạng thái đơn hàng (khớp với % hiển thị ở frontend profileService). */
    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_CANCELLED = 'cancelled';

    /** Danh sách trạng thái hợp lệ (để validate + dropdown). */
    public static function validStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_COMPLETED,
            self::STATUS_CONFIRMED,
            self::STATUS_PROCESSING,
            self::STATUS_SHIPPED,
            self::STATUS_DELIVERED,
            self::STATUS_CANCELLED,
        ];
    }

    /**
     * Options cho dropdown đổi trạng thái (value, label, percent cho progress).
     * Frontend dùng % theo bảng ánh xạ: pending 0%, completed/confirmed 25%, processing 50%, shipped 85%, delivered 100%, cancelled 0%.
     */
    public static function statusOptions(): array
    {
        return [
            ['value' => self::STATUS_PENDING,     'label' => 'Chờ xử lý',       'percent' => 0],
            ['value' => self::STATUS_COMPLETED,   'label' => 'Đã đặt/Thanh toán xong', 'percent' => 25],
            ['value' => self::STATUS_CONFIRMED,   'label' => 'Đã xác nhận',      'percent' => 25],
            ['value' => self::STATUS_PROCESSING,  'label' => 'Đang xử lý/Đóng gói', 'percent' => 50],
            ['value' => self::STATUS_SHIPPED,     'label' => 'Đang giao hàng',   'percent' => 85],
            ['value' => self::STATUS_DELIVERED,   'label' => 'Đã giao',          'percent' => 100],
            ['value' => self::STATUS_CANCELLED,   'label' => 'Đã hủy',          'percent' => 0],
        ];
    }

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
