<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pembelian\PurchaseNotaRequest;
use App\Http\Requests\Pembelian\PurchasePaymentRequest;
use App\Http\Requests\Pembelian\PurchaseRequest;
use App\Http\Requests\Pembelian\PurchaseReturnRequest;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Supplier;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class PurchaseController extends Controller
{
    public function __construct(private StockService $stockService) {}

    public function index(): View
    {
        return view('admin.pembelian.index', [
            'suppliers' => Supplier::active()->orderBy('name')->get(),
            'products' => Product::active()->orderBy('name')->get(['id', 'kode_barang', 'name', 'unit', 'harga_modal']),
        ]);
    }

    public function data(Request $request)
    {
        $query = Purchase::query()->with('supplier:id,name');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return DataTables::eloquent($query)
            ->addColumn('supplier_name', fn (Purchase $p) => $p->supplier->name ?? '-')
            ->addColumn('balance', fn (Purchase $p) => $p->remainingBalance())
            ->addColumn('actions', fn (Purchase $p) => view('admin.pembelian.partials.actions', ['purchase' => $p])->render())
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function store(PurchaseRequest $request): JsonResponse
    {
        $purchase = DB::transaction(function () use ($request) {
            $items = collect($request->validated('items'));
            $total = $items->sum(fn ($item) => $item['qty'] * $item['price']);

            $purchase = Purchase::create([
                'code' => 'PO-'.now()->format('Ymd').'-'.strtoupper(Str::random(4)),
                'supplier_id' => $request->integer('supplier_id'),
                'status' => 'ordered',
                'purchase_date' => $request->date('purchase_date'),
                'total_amount' => $total,
                'notes' => $request->string('notes')->toString() ?: null,
                'user_id' => Auth::id(),
            ]);

            $purchase->items()->createMany($items->map(fn ($item) => [
                'product_id' => $item['product_id'],
                'qty' => $item['qty'],
                'price' => $item['price'],
                'subtotal' => $item['qty'] * $item['price'],
            ])->all());

            return $purchase;
        });

        return response()->json([
            'success' => true,
            'data' => $purchase,
            'message' => "PO {$purchase->code} berhasil dibuat.",
        ], 201);
    }

    public function show(Purchase $purchase): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $purchase->load(['supplier', 'items.product:id,kode_barang,name,unit', 'payments', 'returns.items.product:id,kode_barang,name']),
            'message' => '',
        ]);
    }

    public function markReceived(Purchase $purchase): JsonResponse
    {
        if ($purchase->status === 'received') {
            return response()->json(['success' => false, 'data' => null, 'message' => 'PO ini sudah diterima.'], 422);
        }

        if ($purchase->status === 'cancelled') {
            return response()->json(['success' => false, 'data' => null, 'message' => 'PO yang dibatalkan tidak dapat diterima.'], 422);
        }

        DB::transaction(function () use ($purchase) {
            foreach ($purchase->items()->with('product')->get() as $item) {
                $this->stockService->stockIn($item->product, $item->qty, $purchase, "Pembelian {$purchase->code}");
            }

            $purchase->update(['status' => 'received']);
        });

        return response()->json([
            'success' => true,
            'data' => $purchase->fresh(),
            'message' => "Barang untuk PO {$purchase->code} berhasil diterima dan stok telah diperbarui.",
        ]);
    }

    public function cancel(Purchase $purchase): JsonResponse
    {
        if ($purchase->status === 'received') {
            return response()->json(['success' => false, 'data' => null, 'message' => 'PO yang sudah diterima tidak dapat dibatalkan.'], 422);
        }

        $purchase->update(['status' => 'cancelled']);

        return response()->json(['success' => true, 'data' => $purchase, 'message' => "PO {$purchase->code} dibatalkan."]);
    }

    public function storePayment(PurchasePaymentRequest $request, Purchase $purchase): JsonResponse
    {
        DB::transaction(function () use ($request, $purchase) {
            $purchase->payments()->create($request->validated());
            $purchase->increment('paid_amount', $request->integer('amount'));
        });

        return response()->json([
            'success' => true,
            'data' => $purchase->fresh('payments'),
            'message' => 'Pembayaran berhasil dicatat.',
        ], 201);
    }

    public function uploadNota(PurchaseNotaRequest $request, Purchase $purchase): JsonResponse
    {
        if ($purchase->nota) {
            Storage::disk('public')->delete($purchase->nota);
        }

        $purchase->update(['nota' => $request->file('nota')->store('nota-pembelian', 'public')]);

        return response()->json([
            'success' => true,
            'data' => $purchase,
            'message' => 'Nota berhasil diunggah.',
        ]);
    }

    public function storeReturn(PurchaseReturnRequest $request, Purchase $purchase): JsonResponse
    {
        if ($purchase->status !== 'received') {
            return response()->json([
                'success' => false, 'data' => null,
                'message' => 'Retur hanya dapat dibuat untuk PO yang barangnya sudah diterima.',
            ], 422);
        }

        $return = DB::transaction(function () use ($request, $purchase) {
            $items = collect($request->validated('items'));
            $total = $items->sum(fn ($item) => $item['qty'] * $item['price']);

            $return = PurchaseReturn::create([
                'code' => 'RTR-'.now()->format('Ymd').'-'.strtoupper(Str::random(4)),
                'purchase_id' => $purchase->id,
                'return_date' => $request->date('return_date'),
                'total_amount' => $total,
                'reason' => $request->string('reason')->toString() ?: null,
                'user_id' => Auth::id(),
            ]);

            foreach ($items as $item) {
                $return->items()->create([
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'subtotal' => $item['qty'] * $item['price'],
                ]);

                $product = Product::findOrFail($item['product_id']);
                $this->stockService->stockOut($product, $item['qty'], $return, "Retur pembelian {$return->code}");
            }

            return $return;
        });

        return response()->json([
            'success' => true,
            'data' => $return,
            'message' => "Retur {$return->code} berhasil dibuat dan stok telah dikurangi.",
        ], 201);
    }
}
