<?php

namespace App\Observers;

use App\Models\Category;
use App\Models\Product;
use App\Services\ProductCodeService;

class ProductObserver
{
    public function __construct(private ProductCodeService $codeService) {}

    public function creating(Product $product): void
    {
        if (empty($product->kode_barang)) {
            $category = $product->category ?? Category::findOrFail($product->category_id);
            $product->kode_barang = $this->codeService->generateFor($category);
        }

        // Barcode/QR payload is just the kode_barang — Code128 and QR both
        // encode arbitrary strings, and reusing it means one scan is enough
        // to look a product up, print a label, or open its detail page.
        $product->barcode ??= $product->kode_barang;
        $product->qr_code ??= $product->kode_barang;
    }
}
