<?php

namespace App\Services\Marketplace;

use Illuminate\Support\Collection;

/**
 * Normalizes "get order data from a marketplace" behind one contract,
 * whatever the actual source is — an uploaded file today, a live API call
 * later. Swapping ManualImportSync for ShopeeApiSync/TiktokApiSync should
 * never require touching the code that consumes fetchOrders().
 */
interface MarketplaceSyncInterface
{
    /**
     * @param  array<string, mixed>  $params  Implementation-specific input
     *                                        (e.g. ['file' => UploadedFile] for manual imports).
     * @return Collection<int, array{
     *     marketplace: string,
     *     marketplace_order_id: ?string,
     *     customer_name: ?string,
     *     sale_date: string,
     *     sku: string,
     *     qty: int,
     *     price: int,
     *     shipping_cost: int,
     *     admin_fee: int,
     * }> One row per line item — the caller groups rows into orders by marketplace_order_id.
     */
    public function fetchOrders(array $params = []): Collection;
}
