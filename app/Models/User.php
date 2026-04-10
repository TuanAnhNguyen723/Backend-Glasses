<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Review;
use App\Models\UserPromoCode;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'date_of_birth',
        'gender',
        'is_premium',
        'premium_since',
        'preferred_frame_style',
        'marketing_newsletter',
        'prescription_reminders',
        'language',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'is_premium' => 'boolean',
            'premium_since' => 'datetime',
            'marketing_newsletter' => 'boolean',
            'prescription_reminders' => 'boolean',
        ];
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function promoClaims(): HasMany
    {
        return $this->hasMany(UserPromoCode::class);
    }

    /**
     * URL avatar (signed B2 hoặc /storage cho local).
     */
    public function getAvatarUrlAttribute(): ?string
    {
        if (!$this->avatar) {
            return null;
        }
        if (str_starts_with((string) $this->avatar, 'http')) {
            return $this->avatar;
        }
        if (str_starts_with((string) $this->avatar, 'local:')) {
            return url('/storage/' . substr($this->avatar, 6));
        }
        if (str_starts_with((string) $this->avatar, 'avatars/')) {
            $endpoint = rtrim(config('filesystems.disks.backblaze.endpoint'), '/');
            $bucket = config('filesystems.disks.backblaze.bucket');
            return $endpoint . '/' . $bucket . '/' . $this->avatar;
        }
        return url('/storage/' . $this->avatar);
    }

    /**
     * Kiểm tra user đã mua sản phẩm chưa (đơn đã giao hoặc đã thanh toán xong).
     */
    public function hasPurchasedProduct(int $productId): bool
    {
        $allowedStatuses = [
            Order::STATUS_DELIVERED,
            Order::STATUS_COMPLETED,
            Order::STATUS_CONFIRMED,
            Order::STATUS_SHIPPED,
            Order::STATUS_PROCESSING,
        ];

        return $this->orders()
            ->whereIn('status', $allowedStatuses)
            ->whereHas('items', fn ($q) => $q->where('product_id', $productId))
            ->exists();
    }
}
