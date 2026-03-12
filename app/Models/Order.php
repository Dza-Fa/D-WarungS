<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    // Status Constants
    const STATUS_PENDING = 'pending';

    const STATUS_CONFIRMED = 'confirmed';

    const STATUS_PREPARING = 'preparing';

    const STATUS_READY = 'ready';

    const STATUS_COMPLETED = 'completed';

    const STATUS_CANCELLED = 'cancelled';

    // Payment Status Constants
    const PAYMENT_PENDING = 'pending';

    const PAYMENT_PAID = 'paid';

    const PAYMENT_FAILED = 'failed';

    protected $fillable = [
        'user_id',
        'vendor_id',
        'order_number',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'status',
        'payment_method',
        'payment_status',
        'payment_proof',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    // Helper Methods
    public function canBeConfirmedByCashier(): bool
    {
        return $this->payment_status === self::PAYMENT_PENDING &&
               $this->status === self::STATUS_PENDING;
    }

    public function canBePreparedByVendor(): bool
    {
        return $this->payment_status === self::PAYMENT_PAID &&
               $this->status === self::STATUS_CONFIRMED;
    }

    public function canStartPreparing(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function canMarkReady(): bool
    {
        return $this->status === self::STATUS_PREPARING;
    }

    public function canBePickedUpByCustomer(): bool
    {
        return $this->status === self::STATUS_READY;
    }

    public function isPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_PAID;
    }

    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'bg-yellow-100 text-yellow-800',
            self::STATUS_CONFIRMED => 'bg-blue-100 text-blue-800',
            self::STATUS_PREPARING => 'bg-orange-100 text-orange-800',
            self::STATUS_READY => 'bg-green-100 text-green-800',
            self::STATUS_COMPLETED => 'bg-gray-100 text-gray-800',
            self::STATUS_CANCELLED => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getPaymentStatusBadgeClass(): string
    {
        return match ($this->payment_status) {
            self::PAYMENT_PENDING => 'bg-yellow-100 text-yellow-800',
            self::PAYMENT_PAID => 'bg-green-100 text-green-800',
            self::PAYMENT_FAILED => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
