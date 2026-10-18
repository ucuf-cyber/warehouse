<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Penjualan\SaleRequest;
use App\Models\Product;
use App\Models\Sale;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class SaleController extends Controller
{
    public function __construct(private StockService $stockService) {}

    public function index(): View
    {
        return view('admin.penjualan.index', [
            'products' => Product::active()->orderBy('name')->get(['id', 'kode_barang', 'name', 'unit', 'harga_jual', 'current_stock']),
        ]);
    }

    public function data(Request $request)
    {
        $query = Sale::query();

        if ($request->filled('marketplace')) {
            $query->where('marketplace', $request->string('marketplace'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return DataTables::eloquent($query)
            ->addColumn('actions', fn (Sale $s) => view('admin.penjualan.partials.actions', ['sale' => $s])->render())
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function store(SaleRequest $request): JsonResponse
    {
        $sale = DB::transaction(function () use ($request) {
            $items = collect($request->validated('items'));
            $total = $items->sum(fn ($item) => $item['qty'] * $item['price']);

            $sale = Sale::create([
                'code' => 'SO-'.now()->format('Ymd').'-'.strtoupper(Str::random(5)),
                'marketplace' => 'manual',
                'customer_name' => $request->string('customer_name')->toString() ?: null,
                'status' => 'completed',
                'sale_date' => $request->date('sale_date'),
                'total_amount' => $total,
                'notes' => $request->string('notes')->toString() ?: null,
                'user_id' => Auth::id(),
            ]);

            foreach ($items as $item) {
                $sale->items()->create([
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'subtotal' => $item['qty'] * $item['price'],
                ]);

                $product = Product::findOrFail($item['product_id']);
                $this->stockService->stockOut($product, $item['qty'], $sale, "Penjualan {$sale->code}");
            }

            return $sale;
        });

        return response()->json([
            'success' => true,
            'data' => $sale,
            'message' => "Penjualan {$sale->code} berhasil dicatat.",
        ], 201);
    }

    public function show(Sale $sale): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $sale->load(['items.product:id,kode_barang,name,unit', 'user:id,name']),
            'message' => '',
        ]);
    }

    public function cancel(Sale $sale): JsonResponse
    {
        if ($sale->status === 'cancelled') {
            return response()->json(['success' => false, 'data' => null, 'message' => 'Penjualan ini sudah dibatalkan.'], 422);
        }

        $stockReturned = $sale->status === 'completed';

        DB::transaction(function () use ($sale, $stockReturned) {
            if ($stockReturned) {
                foreach ($sale->items()->with('product')->get() as $item) {
                    $this->stockService->return($item->product, $item->qty, $sale, "Pembatalan penjualan {$sale->code}");
                }
            }

            $sale->update(['status' => 'cancelled']);
        });

        return response()->json([
            'success' => true,
            'data' => $sale->fresh(),
            'message' => "Penjualan {$sale->code} dibatalkan".($stockReturned ? ' dan stok telah dikembalikan.' : '.'),
        ]);
    }
}
