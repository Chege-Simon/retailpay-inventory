<?php

namespace App\Models;

use App\Traits\HandlesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory extends Model
{
    use HandlesUuid;

    protected $table = 'inventories';

    protected $guarded = [];

    protected $casts = [
        'quantity' => 'integer',
        'minimum_stock' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function isLowStock(): bool
    {
        return $this->quantity <= $this->minimum_stock;
    }

    public function adjustQuantity(int $change, string $reason = 'adjustment'): void
    {
        $this->quantity += $change;
        $this->save();
    }
}
