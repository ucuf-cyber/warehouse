<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Fallback product identification for a scanned code that isn't in our own
 * catalog. Queries the free, keyless Open Facts APIs (Open Beauty Facts
 * first — best fit for this store's skincare/cosmetics/supplement
 * categories — then Open Products Facts as a general-goods fallback).
 * Read-only lookups, no account/API key required.
 */
class ExternalProductLookupService
{
    private const ENDPOINTS = [
        'https://world.openbeautyfacts.org/api/v2/product/%s.json',
        'https://world.openproductsfacts.org/api/v2/product/%s.json',
    ];

    /**
     * @return array{name: string, brand: ?string, image_url: ?string, code: string}|null
     */
    public function lookup(string $code): ?array
    {
        foreach (self::ENDPOINTS as $endpointTemplate) {
            $result = $this->query(sprintf($endpointTemplate, urlencode($code)));

            if ($result) {
                return $result;
            }
        }

        return null;
    }

    private function query(string $url): ?array
    {
        try {
            $response = Http::timeout(5)->get($url);
        } catch (Throwable) {
            return null;
        }

        if (! $response->ok() || (int) $response->json('status') !== 1) {
            return null;
        }

        $product = $response->json('product', []);
        $name = $product['product_name'] ?? $product['generic_name'] ?? null;

        if (! $name) {
            return null;
        }

        return [
            'name' => $name,
            'brand' => $product['brands'] ?? null,
            'image_url' => $product['image_front_url'] ?? $product['image_url'] ?? null,
            'code' => $product['code'] ?? '',
        ];
    }
}
