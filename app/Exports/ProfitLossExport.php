<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProfitLossExport implements FromArray, WithHeadings
{
    public function __construct(private array $data) {}

    public function headings(): array
    {
        return ['Komponen', 'Jumlah (Rp)'];
    }

    public function array(): array
    {
        return [
            ['Omzet Penjualan', $this->data['omzet']],
            ['Harga Pokok Penjualan (HPP)', -$this->data['hpp']],
            ['Laba Kotor', $this->data['laba_kotor']],
            ['Beban Operasional', -$this->data['beban_operasional']],
            ['Laba Bersih', $this->data['laba_bersih']],
        ];
    }
}
