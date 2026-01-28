<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockMovement extends Model
{
    protected $guarded = [];

    protected $casts = [
        'quantity_change' => 'integer',
        'quantity_before' => 'integer',
        'quantity_after' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public static function record(
        Product $product,
        Store $store,
        string $type,
        int $quantityChange,
        $reference = null,
        ?User $user = null,
        ?string $notes = null
    ): self {
        $inventory = Inventory::firstOrCreate(
            ['product_id' => $product->id, 'store_id' => $store->id],
            ['quantity' => 0, 'minimum_stock' => 10]
        );

        $quantityBefore = $inventory->quantity;
        $quantityAfter = $quantityBefore + $quantityChange;

        $movement = new self([
            'product_id' => $product->id,
            'store_id' => $store->id,
            'type' => $type,
            'quantity_change' => $quantityChange,
            'quantity_before' => $quantityBefore,
            'quantity_after' => $quantityAfter,
            'user_id' => $user?->id,
            'notes' => $notes,
        ]);

        if ($reference) {
            $movement->reference()->associate($reference);
        }

        $movement->save();

        // Update inventory
        $inventory->quantity = $quantityAfter;
        $inventory->save();

        return $movement;
    }
}
