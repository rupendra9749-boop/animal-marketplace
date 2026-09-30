<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'user_id',
        'status',
        'total',
        'shipping_name',
        'shipping_address',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /** What a buyer or seller reads for each status ("processing" means the seller(s) approved it). */
    public static function statusLabel(string $status): string
    {
        return match ($status) {
            self::STATUS_PENDING => __('Waiting for seller'),
            self::STATUS_PROCESSING => __('Approved'),
            self::STATUS_COMPLETED => __('Completed'),
            self::STATUS_CANCELLED => __('Cancelled'),
            default => ucfirst($status),
        };
    }

    /**
     * Works out the order's status from what its sellers decided: waiting while any seller has not answered,
     * cancelled when every seller declined, approved otherwise. Orders the admin already completed or
     * cancelled are left alone.
     */
    public function syncStatus(): void
    {
        if (in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED], true)) {
            return;
        }

        $statuses = $this->items()->pluck('status');

        if ($statuses->contains(OrderItem::PENDING)) {
            $new = self::STATUS_PENDING;
        } elseif ($statuses->isNotEmpty() && $statuses->every(fn ($status) => $status === OrderItem::DECLINED)) {
            $new = self::STATUS_CANCELLED;
        } else {
            $new = self::STATUS_PROCESSING;
        }

        if ($new !== $this->status) {
            $this->update(['status' => $new]);
        }
    }
}
