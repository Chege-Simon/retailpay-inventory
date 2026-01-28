<?php

namespace App\Models;

use App\Traits\HandlesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Cache;

class Transfer extends Model
{
    use HandlesUuid;

    protected $guarded = [];

    protected $casts = [
        'quantity' => 'integer',
        'transfer_date' => 'datetime',
        'received_date' => 'datetime',
    ];

    public function fromStore(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'from_store_id');
    }

    public function toStore(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'to_store_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function requestedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function forwardedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'forwarded_by');
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function shippedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shipped_by');
    }

    public function receivedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function stockMovements(): MorphMany
    {
        return $this->morphMany(StockMovement::class, 'reference');
    }
    
    public static function generateTransferNumber(): string
    {
        $prefix = 'TRF';
        $date = now()->format('Ymd');
        $cacheKey = 'last-transfer-number';

        $lastTransferNumber = Cache::lock($cacheKey, 10)->block(5, function () {
            return static::whereDate('created_at', today())
                ->latest()
                ->value('transfer_number');
        });

        $sequence = $lastTransferNumber ? (int) substr($lastTransferNumber, -4) + 1 : 1;

        return sprintf('%s-%s-%04d', $prefix, $date, $sequence);
    }

    public function isInterBranch(): bool
    {
        return $this->fromStore?->branch_id !== $this->toStore?->branch_id;
    }
}
