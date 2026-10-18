<?php

namespace App\Services\Marketplace;

use Illuminate\Support\Collection;
use RuntimeException;

/**
 * Skeleton for a future live TikTok Shop Partner API integration. Disabled
 * until TIKTOK_APP_KEY/TIKTOK_APP_SECRET are configured — until then, use
 * ManualImportSync (import the order export from TikTok Seller Center).
 */
class TiktokApiSync implements MarketplaceSyncInterface
{
    public function fetchOrders(array $params = []): Collection
    {
        if (! config('bms.marketplace.tiktok.enabled')) {
            throw new RuntimeException('Integrasi TikTok Shop API belum diaktifkan. Gunakan import manual untuk saat ini.');
        }

        // TODO: call TikTok Shop Partner API's order list + detail endpoints,
        // normalize the response into the same shape ManualImportSync returns.
        return collect();
    }
}
