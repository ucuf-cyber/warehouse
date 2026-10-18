<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Business Monitoring System — application-specific configuration
    |--------------------------------------------------------------------------
    */

    'owner' => [
        'name' => env('OWNER_NAME', 'Owner'),
        'email' => env('OWNER_EMAIL', 'owner@businessmonitoring.local'),
        'password' => env('OWNER_PASSWORD', 'password'),
    ],

    'dashboard_cache_ttl' => (int) env('DASHBOARD_CACHE_TTL', 5),

    'marketplace' => [
        'shopee' => [
            'enabled' => env('MARKETPLACE_SHOPEE_ENABLED', false),
            'partner_id' => env('SHOPEE_PARTNER_ID'),
            'partner_key' => env('SHOPEE_PARTNER_KEY'),
            'shop_id' => env('SHOPEE_SHOP_ID'),
            'redirect_url' => env('SHOPEE_REDIRECT_URL'),
        ],
        'tiktok' => [
            'enabled' => env('MARKETPLACE_TIKTOK_ENABLED', false),
            'app_key' => env('TIKTOK_APP_KEY'),
            'app_secret' => env('TIKTOK_APP_SECRET'),
            'shop_id' => env('TIKTOK_SHOP_ID'),
            'access_token' => env('TIKTOK_ACCESS_TOKEN'),
        ],
    ],

    'barcode_printer' => [
        'width_mm' => (int) env('BARCODE_PRINTER_WIDTH_MM', 40),
        'height_mm' => (int) env('BARCODE_PRINTER_HEIGHT_MM', 25),
    ],

];
