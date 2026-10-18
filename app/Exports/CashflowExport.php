<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CashflowExport implements FromCollection, WithHeadings
{
    public function __construct(private Collection $rows) {}

    public function headings(): array
    {
        return ['Periode', 'Pemasukan', 'Pengeluaran', 'Saldo Kumulatif'];
    }

    public function collection(): Collection
    {
        return $this->rows->map(fn ($row) => [$row['period'], $row['income'], $row['expense'], $row['balance']]);
    }
}
