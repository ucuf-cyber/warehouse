<?php

namespace App\Exports;

use App\Models\StockMovement;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StockMovementsReportExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(private Carbon $from, private Carbon $to) {}

    public function query()
    {
        return StockMovement::query()
            ->with('product:id,kode_barang,name')
            ->whereBetween('created_at', [$this->from->startOfDay(), $this->to->endOfDay()])
            ->orderBy('created_at');
    }

    public function headings(): array
    {
        return ['Tanggal', 'Produk', 'Jenis', 'Qty', 'Stok Sebelum', 'Stok Sesudah', 'Catatan'];
    }

    public function map($movement): array
    {
        return [
            $movement->created_at->format('Y-m-d H:i'),
            ($movement->product->kode_barang ?? '-').' - '.($movement->product->name ?? '-'),
            $movement->type,
            $movement->qty,
            $movement->qty_before,
            $movement->qty_after,
            $movement->notes,
        ];
    }
}
