<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Produk\ProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{
    public function index(): View
    {
        return view('admin.produk.index', [
            'categories' => Category::orderBy('name')->get(),
            'brands' => Brand::orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
        ]);
    }

    public function data(Request $request)
    {
        $query = Product::query()->with(['category', 'brand', 'supplier']);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->boolean('low_stock')) {
            $query->belowMinimumStock();
        }

        return DataTables::eloquent($query)
            ->addColumn('category_name', fn (Product $p) => $p->category->name ?? '-')
            ->addColumn('brand_name', fn (Product $p) => $p->brand->name ?? '-')
            ->editColumn('harga_modal', fn (Product $p) => (int) $p->harga_modal)
            ->editColumn('harga_jual', fn (Product $p) => (int) $p->harga_jual)
            ->addColumn('actions', fn (Product $p) => view('admin.produk.partials.actions', ['product' => $p])->render())
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function store(ProductRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['current_stock'] = 0;

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('produk', 'public');
        }

        $product = Product::create($data);

        return response()->json([
            'success' => true,
            'data' => $product,
            'message' => "Produk berhasil ditambahkan dengan kode {$product->kode_barang}.",
        ], 201);
    }

    public function show(Product $product): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $product->load(['category', 'brand', 'supplier']),
            'message' => '',
        ]);
    }

    public function update(ProductRequest $request, Product $product): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('foto')) {
            if ($product->foto) {
                Storage::disk('public')->delete($product->foto);
            }

            $data['foto'] = $request->file('foto')->store('produk', 'public');
        }

        $product->update($data);

        return response()->json([
            'success' => true,
            'data' => $product,
            'message' => 'Produk berhasil diperbarui.',
        ]);
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Produk berhasil dihapus.',
        ]);
    }
}
