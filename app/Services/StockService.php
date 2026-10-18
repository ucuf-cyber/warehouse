<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

/**
 * The only place that should ever change a product's stock. Every call
 * writes a stock_movements row (the source of truth) and updates
 * products.current_stock (a cache) inside the same locked transaction, so
 * the two can never drift apart even under concurrent requests.
 */
class StockService
{
    public function move(
        Product $product,
        string $type,
        int $qty,
        ?Model $reference = null,
        ?string $notes = null,
        ?int $userId = null,
    ): StockMovement {
        $movement = DB::transaction(function () use ($product, $type, $qty, $reference, $notes, $userId) {
            $locked = Product::whereKey($product->id)->lockForUpdate()->first();

            $before = $locked->current_stock;
            $after = $before + $qty;

            if ($after < 0) {
                throw new InsufficientStockException(
                    "Stok {$locked->name} tidak mencukupi (tersedia {$before}, diminta ".abs($qty).')'
                );
            }

            $locked->update(['current_stock' => $after]);

            return StockMovement::create([
                'product_id' => $locked->id,
                'type' => $type,
                'qty' => $qty,
                'qty_before' => $before,
                'qty_after' => $after,
                'reference_type' => $reference?->getMorphClass(),
                'reference_id' => $reference?->getKey(),
                'notes' => $notes,
                'user_id' => $userId ?? Auth::id(),
            ]);
        });

        // Notify only on the crossing (above min → at/below min), not on every
        // subsequent movement while a product stays low — that would be noisy.
        if ($movement->qty_after <= Product::MIN_STOCK_THRESHOLD && $movement->qty_before > Product::MIN_STOCK_THRESHOLD) {
            Notification::send(User::all(), new LowStockNotification($product->fresh()));
        }

        return $movement;
    }

    public function stockIn(Product $product, int $qty, ?Model $reference = null, ?string $notes = null): StockMovement
    {
        return $this->move($product, 'in', abs($qty), $reference, $notes);
    }

    public function stockOut(Product $product, int $qty, ?Model $reference = null, ?string $notes = null): StockMovement
    {
        return $this->move($product, 'out', -abs($qty), $reference, $notes);
    }

    public function damage(Product $product, int $qty, ?string $notes = null): StockMovement
    {
        return $this->move($product, 'damage', -abs($qty), null, $notes);
    }

    public function lost(Product $product, int $qty, ?string $notes = null): StockMovement
    {
        return $this->move($product, 'lost', -abs($qty), null, $notes);
    }

    public function return(Product $product, int $qty, ?Model $reference = null, ?string $notes = null): StockMovement
    {
        return $this->move($product, 'return', abs($qty), $reference, $notes);
    }

    /**
     * Sets stock to an exact figure (e.g. a manual correction or the result
     * of a stock opname) rather than adding/removing a delta.
     */
    public function adjustTo(Product $product, int $newQty, ?Model $reference = null, ?string $notes = null): StockMovement
    {
        $diff = $newQty - $product->current_stock;
        $type = $reference instanceof \App\Models\StockOpname ? 'opname' : 'adjustment';

        return $this->move($product, $type, $diff, $reference, $notes);
    }
}
