<?php

namespace App\Services\Marketplace;

use Illuminate\Support\Collection;
use RuntimeException;

/**
 * Skeleton for a future live Shopee Open Platform integration. Disabled
 * until SHOPEE_PARTNER_ID/SHOPEE_PARTNER_KEY are configured — until then,
 * use ManualImportSync (import the order export from Shopee Seller Center).
 */
class ShopeeApiSync implements MarketplaceSyncInterface
{
    public function fetchOrders(array $params = []): Collection
    {
        if (! config('bms.marketplace.shopee.enabled')) {
            throw new RuntimeException('Integrasi Shopee API belum diaktifkan. Gunakan import manual untuk saat ini.');
        }

        // TODO: call Shopee Open Platform's order list + order detail APIs,
        // normalize the response into the same shape ManualImportSync returns.
        return collect();
    }
}
