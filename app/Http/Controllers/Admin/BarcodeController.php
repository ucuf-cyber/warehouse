<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BarcodeController extends Controller
{
    public function print(Request $request): View
    {
        $ids = collect(explode(',', (string) $request->query('ids')))
            ->map(fn ($id) => (int) trim($id))
            ->filter()
            ->unique();

        $products = Product::whereIn('id', $ids)->orderBy('name')->get();

        return view('admin.produk.barcode-print', [
            'products' => $products,
            'type' => $request->query('type', 'barcode') === 'qr' ? 'qr' : 'barcode',
        ]);
    }
}
