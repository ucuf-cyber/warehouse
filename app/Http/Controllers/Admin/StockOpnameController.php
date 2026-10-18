<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Stok\StockOpnameItemRequest;
use App\Models\Product;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StockOpnameController extends Controller
{
    public function __construct(private StockService $stockService) {}

    public function index(): View
    {
        return view('admin.stok.opname.index', [
            'sessions' => StockOpname::withCount(['items', 'items as checked_count' => fn ($q) => $q->where('status', 'checked')])
                ->with('user:id,name')
                ->latest()
                ->get(),
        ]);
    }

    public function store(): JsonResponse
    {
        $opname = DB::transaction(function () {
            $opname = StockOpname::create([
                'code' => 'SO-'.now()->format('Ymd').'-'.strtoupper(Str::random(4)),
                'status' => 'in_progress',
                'started_at' => now(),
                'user_id' => Auth::id(),
            ]);

            $products = Product::active()->get(['id', 'current_stock']);

            $items = $products->map(fn (Product $p) => [
                'stock_opname_id' => $opname->id,
                'product_id' => $p->id,
                'system_qty' => $p->current_stock,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            StockOpnameItem::insert($items->all());

            return $opname;
        });

        return response()->json([
            'success' => true,
            'data' => $opname,
            'message' => "Sesi stock opname {$opname->code} berhasil dibuat.",
        ], 201);
    }

    public function show(StockOpname $opname): View
    {
        return view('admin.stok.opname.show', [
            'opname' => $opname->load('items.product:id,kode_barang,name,unit,barcode,qr_code'),
        ]);
    }

    public function findItem(StockOpname $opname, string $code): JsonResponse
    {
        $item = $opname->items()
            ->with('product:id,kode_barang,name,unit,barcode,qr_code')
            ->whereHas('product', fn ($q) => $q->where('barcode', $code)->orWhere('qr_code', $code)->orWhere('kode_barang', $code))
            ->first();

        if (! $item) {
            return response()->json(['success' => false, 'data' => null, 'message' => "Produk \"{$code}\" tidak ada dalam sesi ini."], 404);
        }

        return response()->json(['success' => true, 'data' => $item, 'message' => '']);
    }

    public function updateItem(StockOpnameItemRequest $request, StockOpname $opname, StockOpnameItem $item): JsonResponse
    {
        abort_if($item->stock_opname_id !== $opname->id, 404);

        $physicalQty = $request->integer('physical_qty');

        $item->update([
            'physical_qty' => $physicalQty,
            'difference' => $physicalQty - $item->system_qty,
            'status' => 'checked',
            'checked_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $item->fresh(),
            'message' => 'Item berhasil dicatat.',
            'progress' => $opname->progress(),
        ]);
    }

    public function finalize(StockOpname $opname): JsonResponse
    {
        if ($opname->status === 'completed') {
            return response()->json(['success' => false, 'data' => null, 'message' => 'Sesi ini sudah difinalisasi.'], 422);
        }

        DB::transaction(function () use ($opname) {
            $checkedItems = $opname->items()->where('status', 'checked')->with('product')->get();

            foreach ($checkedItems as $item) {
                if ($item->difference !== 0) {
                    $this->stockService->adjustTo(
                        $item->product,
                        $item->physical_qty,
                        reference: $opname,
                        notes: "Stock Opname {$opname->code}",
                    );
                }
            }

            $opname->update(['status' => 'completed', 'completed_at' => now()]);
        });

        return response()->json([
            'success' => true,
            'data' => $opname->fresh(),
            'message' => "Stock opname {$opname->code} berhasil difinalisasi.",
        ]);
    }
}
