<?php

namespace App\Exports;

use App\Models\Sale;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SalesReportExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(private Carbon $from, private Carbon $to) {}

    public function query()
    {
        return Sale::query()
            ->whereBetween('sale_date', [$this->from->toDateString(), $this->to->toDateString()])
            ->orderBy('sale_date');
    }

    public function headings(): array
    {
        return ['Kode Order', 'Marketplace', 'Pelanggan', 'Tanggal', 'Total', 'Ongkir', 'Biaya Admin', 'Status'];
    }

    public function map($sale): array
    {
        return [
            $sale->code,
            ucfirst($sale->marketplace),
            $sale->customer_name,
            $sale->sale_date->format('Y-m-d'),
            $sale->total_amount,
            $sale->shipping_cost,
            $sale->admin_fee,
            ucfirst($sale->status),
        ];
    }
}
