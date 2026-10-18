<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\ExternalProductLookupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(private ExternalProductLookupService $externalLookup) {}

    /**
     * Looks a product up by whatever a scan produced — camera (html5-qrcode)
     * and USB keyboard-wedge scanners both just hand us a raw string, which
     * could be the barcode, the QR payload, or the kode_barang itself since
     * all three currently share the same value. If it's not in our own
     * catalog, falls back to a free external product database (see
     * ExternalProductLookupService) so staff can still identify an
     * unregistered item by scan alone.
     */
    public function scan(Request $request): JsonResponse
    {
        $code = trim((string) $request->query('code'));

        if ($code === '') {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Kode tidak boleh kosong.',
            ], 422);
        }

        $product = Product::query()
            ->with(['category', 'brand', 'subRack.rack'])
            ->where('barcode', $code)
            ->orWhere('qr_code', $code)
            ->orWhere('kode_barang', $code)
            ->first();

        if ($product) {
            return response()->json([
                'success' => true,
                'data' => $product,
                'source' => 'internal',
                'message' => '',
            ]);
        }

        $external = $this->externalLookup->lookup($code);

        if ($external) {
            return response()->json([
                'success' => true,
                'data' => $external,
                'source' => 'external',
                'message' => 'Produk belum terdaftar di sistem — ditemukan via database eksternal.',
            ]);
        }

        return response()->json([
            'success' => false,
            'data' => null,
            'message' => "Produk dengan kode \"{$code}\" tidak ditemukan.",
        ], 404);
    }
}
