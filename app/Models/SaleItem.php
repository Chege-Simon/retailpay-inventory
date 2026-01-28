<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    protected $guarded = [];
    
    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($saleItem) {
            $saleItem->subtotal = $saleItem->quantity * $saleItem->unit_price;
        });

        static::updating(function ($saleItem) {
            $saleItem->subtotal = $saleItem->quantity * $saleItem->unit_price;
        });
    }
}
