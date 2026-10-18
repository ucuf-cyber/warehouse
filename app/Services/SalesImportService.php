<?php

namespace App\Services;

use App\Models\MarketplaceSkuMapping;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Notifications\NewSaleNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Throwable;

/**
 * Turns the normalized rows a MarketplaceSyncInterface implementation hands
 * back into real Sale + SaleItem records, resolving each row's marketplace
 * SKU to an internal product and immediately deducting stock — imported
 * rows represent orders the marketplace already considers settled.
 */
class SalesImportService
{
    public function __construct(private StockService $stockService) {}

    /**
     * @return array{created: int, skipped: array<int, string>}
     */
    public function import(Collection $rows): array
    {
        $created = 0;
        $skipped = [];

        $orders = $rows->groupBy(fn ($row) => $row['marketplace_order_id'] ?: 'row-'.Str::random(8));

        foreach ($orders as $orderId => $group) {
            try {
                DB::transaction(function () use ($group) {
                    $this->createOrder($group);
                });
                $created++;
            } catch (Throwable $e) {
                $skipped[] = "Order {$orderId}: {$e->getMessage()}";
            }
        }

        return ['created' => $created, 'skipped' => $skipped];
    }

    private function createOrder(Collection $rows): void
    {
        $first = $rows->first();

        $lineItems = $rows->map(function ($row) {
            $product = $this->resolveProduct($row['sku'], $row['marketplace']);

            if (! $product) {
                throw new \RuntimeException("SKU \"{$row['sku']}\" tidak dikenali.");
            }

            return ['product' => $product, 'qty' => $row['qty'], 'price' => $row['price']];
        });

        $subtotal = $lineItems->sum(fn ($i) => $i['qty'] * $i['price']);
        $shippingCost = (int) $rows->max('shipping_cost');
        $adminFee = (int) $rows->max('admin_fee');

        $sale = Sale::create([
            'code' => 'SO-'.now()->format('Ymd').'-'.strtoupper(Str::random(5)),
            'marketplace' => $first['marketplace'],
            'marketplace_order_id' => $first['marketplace_order_id'],
            'customer_name' => $first['customer_name'],
            'status' => 'completed',
            'sale_date' => $first['sale_date'],
            'total_amount' => $subtotal + $shippingCost,
            'shipping_cost' => $shippingCost,
            'admin_fee' => $adminFee,
            'user_id' => Auth::id(),
        ]);

        foreach ($lineItems as $item) {
            $sale->items()->create([
                'product_id' => $item['product']->id,
                'qty' => $item['qty'],
                'price' => $item['price'],
                'subtotal' => $item['qty'] * $item['price'],
            ]);

            $this->stockService->stockOut($item['product'], $item['qty'], $sale, "Import penjualan {$sale->code}");
        }

        // Only imported marketplace orders get notified — the Owner already
        // knows about sales they just typed in through the manual form.
        if ($sale->marketplace !== 'manual') {
            Notification::send(User::all(), new NewSaleNotification($sale));
        }
    }

    private function resolveProduct(string $sku, string $marketplace): ?Product
    {
        if ($marketplace !== 'manual') {
            $mapped = MarketplaceSkuMapping::query()
                ->where('marketplace', $marketplace)
                ->where('marketplace_sku', $sku)
                ->first()?->product;

            if ($mapped) {
                return $mapped;
            }
        }

        return Product::query()
            ->where('kode_barang', $sku)
            ->orWhere('sku', $sku)
            ->orWhere('barcode', $sku)
            ->first();
    }
}
