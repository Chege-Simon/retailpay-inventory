<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Sale extends Model
{
    protected $guarded = [];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'sale_date' => 'datetime',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function stockMovements(): MorphMany
    {
        return $this->morphMany(StockMovement::class, 'reference');
    }

    public static function generateSaleNumber(): string
    {
        $prefix = 'SAL';
        $date = now()->format('Ymd');
        $lastSale = static::whereDate('created_at', today())->latest()->first();
        
        $sequence = $lastSale ? (int)substr($lastSale->sale_number, -4) + 1 : 1;
        
        return sprintf('%s-%s-%04d', $prefix, $date, $sequence);
    }

    public function calculateTotal(): void
    {
        $this->total_amount = $this->items()->sum('subtotal');
        $this->save();
    }
}
