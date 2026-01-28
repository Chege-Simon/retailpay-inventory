<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $guarded = [];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function inventory(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(Transfer::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function getStockInStore(Store $store): int
    {
        $inventory = $this->inventory()->where('store_id', $store->id)->first();
        return $inventory ? $inventory->quantity : 0;
    }

    public function getTotalStock(): int
    {
        return $this->inventory()->sum('quantity');
    }
}
