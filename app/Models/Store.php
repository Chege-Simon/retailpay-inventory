<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Store extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function inventory(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function transfersOut(): HasMany
    {
        return $this->hasMany(Transfer::class, 'from_store_id');
    }

    public function transfersIn(): HasMany
    {
        return $this->hasMany(Transfer::class, 'to_store_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function manager()
    {
        return $this->hasOne(User::class)->where('role', 'store_manager');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
}
