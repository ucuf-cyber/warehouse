<?php

namespace App\Http\Controllers\Admin;

use App\Exports\SalesTemplateExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Penjualan\MarketplaceSkuMappingRequest;
use App\Models\MarketplaceSkuMapping;
use App\Models\Product;
use App\Services\Marketplace\ManualImportSync;
use App\Services\Marketplace\MarketplaceSyncInterface;
use App\Services\Marketplace\ShopeeApiSync;
use App\Services\Marketplace\TiktokApiSync;
use App\Services\SalesImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class MarketplaceController extends Controller
{
    public function index(): View
    {
        return view('admin.marketplace.index', [
            'products' => Product::active()->orderBy('name')->get(['id', 'kode_barang', 'name']),
            'shopeeEnabled' => Config::get('bms.marketplace.shopee.enabled'),
            'tiktokEnabled' => Config::get('bms.marketplace.tiktok.enabled'),
        ]);
    }

    public function template()
    {
        return Excel::download(new SalesTemplateExport, 'template-import-penjualan.xlsx');
    }

    public function import(Request $request, SalesImportService $importService): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
            'source' => ['required', 'in:manual,shopee,tiktok'],
        ]);

        $sync = $this->resolveSync($request->string('source')->toString());

        try {
            $rows = $sync->fetchOrders(['file' => $request->file('file')]);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'data' => null, 'message' => $e->getMessage()], 422);
        }

        if ($rows->isEmpty()) {
            return response()->json(['success' => false, 'data' => null, 'message' => 'Tidak ada baris valid ditemukan di file.'], 422);
        }

        $result = $importService->import($rows);

        $message = "{$result['created']} pesanan berhasil diimpor.";
        if (! empty($result['skipped'])) {
            $message .= ' '.count($result['skipped']).' baris dilewati (lihat detail).';
        }

        return response()->json([
            'success' => true,
            'data' => $result,
            'message' => $message,
        ]);
    }

    private function resolveSync(string $source): MarketplaceSyncInterface
    {
        return match ($source) {
            'shopee' => app(ShopeeApiSync::class),
            'tiktok' => app(TiktokApiSync::class),
            default => app(ManualImportSync::class),
        };
    }

    public function mappingData(): JsonResponse
    {
        $mappings = MarketplaceSkuMapping::with('product:id,kode_barang,name')->orderByDesc('id')->get();

        return response()->json(['success' => true, 'data' => $mappings, 'message' => '']);
    }

    public function mappingStore(MarketplaceSkuMappingRequest $request): JsonResponse
    {
        $mapping = MarketplaceSkuMapping::create($request->validated());

        return response()->json(['success' => true, 'data' => $mapping, 'message' => 'Mapping SKU berhasil ditambahkan.'], 201);
    }

    public function mappingShow(MarketplaceSkuMapping $mapping): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $mapping, 'message' => '']);
    }

    public function mappingUpdate(MarketplaceSkuMappingRequest $request, MarketplaceSkuMapping $mapping): JsonResponse
    {
        $mapping->update($request->validated());

        return response()->json(['success' => true, 'data' => $mapping, 'message' => 'Mapping SKU berhasil diperbarui.']);
    }

    public function mappingDestroy(MarketplaceSkuMapping $mapping): JsonResponse
    {
        $mapping->delete();

        return response()->json(['success' => true, 'data' => null, 'message' => 'Mapping SKU berhasil dihapus.']);
    }
}
