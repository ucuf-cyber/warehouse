<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SalesTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return ['kode_barang', 'qty', 'harga', 'marketplace', 'no_pesanan', 'nama_pelanggan', 'tanggal', 'ongkir', 'biaya_admin'];
    }

    public function array(): array
    {
        return [
            ['SKN-000001', 2, 45000, 'manual', '', 'Budi Santoso', now()->toDateString(), 0, 0],
            ['HRB-000002', 1, 90000, 'shopee', 'SPX123456789', 'Siti Aminah', now()->toDateString(), 10000, 4500],
        ];
    }
}
