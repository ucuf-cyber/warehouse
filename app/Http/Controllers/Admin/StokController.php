<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Stok\StockAdjustmentRequest;
use App\Http\Requests\Stok\StockInRequest;
use App\Http\Requests\Stok\StockMutasiRequest;
use App\Http\Requests\Stok\StockOutRequest;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\SubRack;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class StokController extends Controller
{
    public function __construct(private StockService $stockService) {}

    public function index(): View
    {
        return view('admin.stok.index', [
            'products' => Product::active()->orderBy('name')->get(['id', 'kode_barang', 'name', 'unit', 'current_stock', 'sub_rack_id']),
            'subRacks' => SubRack::with('rack')->get(),
        ]);
    }

    public function data(Request $request)
    {
        $query = StockMovement::query()->with(['product:id,kode_barang,name,unit', 'user:id,name']);

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->integer('product_id'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date('date_to'));
        }

        return DataTables::eloquent($query)
            ->addColumn('product_name', fn (StockMovement $m) => $m->product->name.' ('.$m->product->kode_barang.')')
            ->addColumn('user_name', fn (StockMovement $m) => $m->user->name ?? 'System')
            ->addColumn('type_label', fn (StockMovement $m) => $this->typeLabel($m->type))
            ->toJson();
    }

    private function typeLabel(string $type): string
    {
        return match ($type) {
            'in' => 'Masuk',
            'out' => 'Keluar',
            'adjustment' => 'Penyesuaian',
            'opname' => 'Stock Opname',
            'damage' => 'Rusak',
            'lost' => 'Hilang',
            'return' => 'Retur',
            default => ucfirst($type),
        };
    }

    public function storeIn(StockInRequest $request): JsonResponse
    {
        $product = Product::findOrFail($request->integer('product_id'));

        $movement = $this->stockService->stockIn($product, $request->integer('qty'), notes: $request->string('notes')->toString() ?: null);

        return response()->json([
            'success' => true,
            'data' => $movement,
            'message' => "Stok {$product->name} berhasil ditambahkan.",
        ], 201);
    }

    public function storeOut(StockOutRequest $request): JsonResponse
    {
        $product = Product::findOrFail($request->integer('product_id'));
        $notes = $request->string('notes')->toString() ?: null;

        $movement = match ($request->string('type')->toString()) {
            'damage' => $this->stockService->damage($product, $request->integer('qty'), $notes),
            'lost' => $this->stockService->lost($product, $request->integer('qty'), $notes),
            default => $this->stockService->stockOut($product, $request->integer('qty'), notes: $notes),
        };

        return response()->json([
            'success' => true,
            'data' => $movement,
            'message' => "Stok {$product->name} berhasil dikurangi.",
        ], 201);
    }

    public function storeAdjustment(StockAdjustmentRequest $request): JsonResponse
    {
        $product = Product::findOrFail($request->integer('product_id'));

        $movement = $this->stockService->adjustTo(
            $product,
            $request->integer('physical_qty'),
            notes: $request->string('notes')->toString() ?: null,
        );

        return response()->json([
            'success' => true,
            'data' => $movement,
            'message' => "Stok {$product->name} berhasil disesuaikan.",
        ], 201);
    }

    public function storeMutasi(StockMutasiRequest $request): JsonResponse
    {
        $product = Product::findOrFail($request->integer('product_id'));
        $product->update(['sub_rack_id' => $request->integer('sub_rack_id')]);

        return response()->json([
            'success' => true,
            'data' => $product,
            'message' => "Lokasi {$product->name} berhasil dipindahkan.",
        ]);
    }
}
