<?php

namespace App\Jobs;

use App\Exports\PurchasesReportExport;
use App\Exports\SalesReportExport;
use App\Exports\StockMovementsReportExport;
use App\Models\Purchase;
use App\Models\ReportLog;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Notifications\ReportReadyNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

/**
 * Generates the row-level reports (Penjualan, Pembelian, Stok) in the
 * background so a large date range never times out an HTTP request, then
 * notifies the Owner via the bell icon once the file is ready.
 */
class ExportReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $reportLogId,
        public string $type,
        public string $format,
        public string $from,
        public string $to,
    ) {}

    public function handle(): void
    {
        $reportLog = ReportLog::findOrFail($this->reportLogId);
        $from = Carbon::parse($this->from)->startOfDay();
        $to = Carbon::parse($this->to)->endOfDay();

        try {
            $fileName = "{$this->type}-{$from->format('Ymd')}-{$to->format('Ymd')}-".Str::lower(Str::random(6));
            $path = $this->format === 'excel'
                ? $this->generateExcel($fileName, $from, $to)
                : $this->generatePdf($fileName, $from, $to);

            $reportLog->update(['status' => 'completed', 'file_path' => $path, 'completed_at' => now()]);

            $reportLog->requestedBy?->notify(new ReportReadyNotification($reportLog));
        } catch (Throwable $e) {
            $reportLog->update(['status' => 'failed']);
            throw $e;
        }
    }

    private function generateExcel(string $fileName, Carbon $from, Carbon $to): string
    {
        $export = match ($this->type) {
            'penjualan' => new SalesReportExport($from, $to),
            'pembelian' => new PurchasesReportExport($from, $to),
            'stok' => new StockMovementsReportExport($from, $to),
        };

        $path = "laporan/{$fileName}.xlsx";
        Excel::store($export, $path, 'public');

        return $path;
    }

    private function generatePdf(string $fileName, Carbon $from, Carbon $to): string
    {
        [$headers, $rows] = match ($this->type) {
            'penjualan' => [
                ['Kode Order', 'Marketplace', 'Pelanggan', 'Tanggal', 'Total', 'Status'],
                Sale::whereBetween('sale_date', [$from->toDateString(), $to->toDateString()])->orderBy('sale_date')->get()
                    ->map(fn (Sale $s) => [$s->code, ucfirst($s->marketplace), $s->customer_name, $s->sale_date->format('d/m/Y'), 'Rp '.number_format($s->total_amount, 0, ',', '.'), ucfirst($s->status)]),
            ],
            'pembelian' => [
                ['Kode PO', 'Supplier', 'Tanggal', 'Total', 'Status'],
                Purchase::with('supplier:id,name')->whereBetween('purchase_date', [$from->toDateString(), $to->toDateString()])->orderBy('purchase_date')->get()
                    ->map(fn (Purchase $p) => [$p->code, $p->supplier->name ?? '-', $p->purchase_date->format('d/m/Y'), 'Rp '.number_format($p->total_amount, 0, ',', '.'), ucfirst($p->status)]),
            ],
            'stok' => [
                ['Tanggal', 'Produk', 'Jenis', 'Qty', 'Stok Sesudah'],
                StockMovement::with('product:id,kode_barang,name')->whereBetween('created_at', [$from, $to])->orderBy('created_at')->get()
                    ->map(fn (StockMovement $m) => [$m->created_at->format('d/m/Y H:i'), $m->product->name ?? '-', $m->type, $m->qty, $m->qty_after]),
            ],
        };

        $pdf = Pdf::loadView('laporan.pdf.tabular', [
            'title' => 'Laporan '.ucfirst($this->type),
            'period' => $from->format('d M Y').' – '.$to->format('d M Y'),
            'headers' => $headers,
            'rows' => $rows,
        ]);

        $path = "laporan/{$fileName}.pdf";
        Storage::disk('public')->put($path, $pdf->output());

        return $path;
    }
}
