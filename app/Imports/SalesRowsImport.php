<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Reads the downloadable sales template. Column headers (row 1) map to the
 * array keys below via WithHeadingRow's snake_case normalization.
 *
 * kode_barang | qty | harga | marketplace | no_pesanan | nama_pelanggan | tanggal | ongkir | biaya_admin
 */
class SalesRowsImport implements ToCollection, WithHeadingRow
{
    public Collection $rows;

    public function collection(Collection $rows): void
    {
        $this->rows = $rows;
    }
}
