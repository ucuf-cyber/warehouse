<?php

namespace App\Services\Marketplace;

use App\Imports\SalesRowsImport;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use RuntimeException;

/**
 * The only sync currently wired up: reads a spreadsheet the Owner exported
 * from Shopee/TikTok Seller Center (or filled in by hand) using our
 * downloadable template. See resources/views/admin/marketplace for the
 * template columns.
 */
class ManualImportSync implements MarketplaceSyncInterface
{
    public function fetchOrders(array $params = []): Collection
    {
        /** @var UploadedFile|null $file */
        $file = $params['file'] ?? null;

        if (! $file) {
            throw new RuntimeException('File import tidak ditemukan.');
        }

        $import = new SalesRowsImport;
        Excel::import($import, $file);

        return $import->rows
            ->filter(fn ($row) => ! empty($row['kode_barang']))
            ->map(fn ($row) => [
                'marketplace' => in_array($row['marketplace'] ?? null, ['manual', 'shopee', 'tiktok'], true)
                    ? $row['marketplace']
                    : 'manual',
                'marketplace_order_id' => $row['no_pesanan'] ?? null,
                'customer_name' => $row['nama_pelanggan'] ?? null,
                'sale_date' => $this->parseDate($row['tanggal'] ?? null),
                'sku' => (string) $row['kode_barang'],
                'qty' => (int) ($row['qty'] ?? 0),
                'price' => (int) ($row['harga'] ?? 0),
                'shipping_cost' => (int) ($row['ongkir'] ?? 0),
                'admin_fee' => (int) ($row['biaya_admin'] ?? 0),
            ])
            ->values();
    }

    private function parseDate(mixed $value): string
    {
        if (empty($value)) {
            return now()->toDateString();
        }

        if (is_numeric($value)) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
        }

        return Carbon::parse($value)->toDateString();
    }
}
