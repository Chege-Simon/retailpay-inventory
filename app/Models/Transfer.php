<?php

namespace App\Models;

use App\Traits\HandlesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

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

    public function initiatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
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
        $lastTransfer = static::whereDate('created_at', today())->latest()->first();
        
        $sequence = $lastTransfer ? (int)substr($lastTransfer->transfer_number, -4) + 1 : 1;
        
        return sprintf('%s-%s-%04d', $prefix, $date, $sequence);
    }

    public function isInterBranch(): bool
    {
        return $this->fromStore->branch_id !== $this->toStore->branch_id;
    }
}
