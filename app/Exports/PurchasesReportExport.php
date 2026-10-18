<?php

namespace App\Exports;

use App\Models\Purchase;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PurchasesReportExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(private Carbon $from, private Carbon $to) {}

    public function query()
    {
        return Purchase::query()
            ->with('supplier:id,name')
            ->whereBetween('purchase_date', [$this->from->toDateString(), $this->to->toDateString()])
            ->orderBy('purchase_date');
    }

    public function headings(): array
    {
        return ['Kode PO', 'Supplier', 'Tanggal', 'Total', 'Dibayar', 'Sisa', 'Status'];
    }

    public function map($purchase): array
    {
        return [
            $purchase->code,
            $purchase->supplier->name ?? '-',
            $purchase->purchase_date->format('Y-m-d'),
            $purchase->total_amount,
            $purchase->paid_amount,
            $purchase->remainingBalance(),
            ucfirst($purchase->status),
        ];
    }
}
