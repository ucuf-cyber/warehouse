<?php

namespace App\Http\Controllers\Admin;

use App\Exports\CashflowExport;
use App\Exports\ProfitLossExport;
use App\Http\Controllers\Controller;
use App\Jobs\ExportReportJob;
use App\Models\ReportLog;
use App\Services\FinanceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function __construct(private FinanceService $financeService) {}

    private const QUEUED_TYPES = ['penjualan', 'pembelian', 'stok'];
    private const SYNC_TYPES = ['laba-rugi', 'cashflow', 'neraca'];

    public function index(): View
    {
        return view('admin.laporan.index', [
            'history' => ReportLog::with('requestedBy:id,name')->latest()->take(20)->get(),
        ]);
    }

    public function history(): JsonResponse
    {
        $history = ReportLog::with('requestedBy:id,name')->latest()->take(20)->get();

        return response()->json(['success' => true, 'data' => $history, 'message' => '']);
    }

    /**
     * Small aggregate reports (Laba Rugi, Cashflow, Neraca) — generated and
     * downloaded immediately, no queue needed.
     */
    public function exportSync(Request $request): Response
    {
        $request->validate([
            'type' => ['required', 'in:laba-rugi,cashflow,neraca'],
            'format' => ['required', 'in:pdf,excel'],
        ]);

        $to = $request->filled('to') ? Carbon::parse($request->string('to')) : now();
        $from = $request->filled('from') ? Carbon::parse($request->string('from')) : $to->copy()->subMonth();
        $type = $request->string('type')->toString();
        $fileName = "{$type}-{$from->format('Ymd')}-{$to->format('Ymd')}";

        return match ([$type, $request->string('format')->toString()]) {
            ['laba-rugi', 'pdf'] => Pdf::loadView('laporan.pdf.laba-rugi', [
                'title' => 'Laporan Laba Rugi',
                'period' => $from->format('d M Y').' – '.$to->format('d M Y'),
                'data' => $this->financeService->profitLoss($from, $to),
            ])->download("{$fileName}.pdf"),
            ['laba-rugi', 'excel'] => Excel::download(new ProfitLossExport($this->financeService->profitLoss($from, $to)), "{$fileName}.xlsx"),
            ['neraca', 'pdf'] => Pdf::loadView('laporan.pdf.neraca', [
                'title' => 'Neraca Sederhana',
                'data' => $this->financeService->balanceSheet(),
            ])->download("{$fileName}.pdf"),
            ['neraca', 'excel'] => Excel::download(new ProfitLossExport(['omzet' => 0, 'hpp' => 0, 'laba_kotor' => 0, 'beban_operasional' => 0, 'laba_bersih' => 0]), "{$fileName}.xlsx"),
            ['cashflow', 'excel'] => Excel::download(new CashflowExport($this->financeService->cashflow($from, $to)), "{$fileName}.xlsx"),
            ['cashflow', 'pdf'] => Pdf::loadView('laporan.pdf.tabular', [
                'title' => 'Laporan Cashflow',
                'period' => $from->format('d M Y').' – '.$to->format('d M Y'),
                'headers' => ['Periode', 'Pemasukan', 'Pengeluaran', 'Saldo'],
                'rows' => $this->financeService->cashflow($from, $to)->map(fn ($r) => [
                    $r['period'], 'Rp '.number_format($r['income'], 0, ',', '.'), 'Rp '.number_format($r['expense'], 0, ',', '.'), 'Rp '.number_format($r['balance'], 0, ',', '.'),
                ]),
            ])->download("{$fileName}.pdf"),
        };
    }

    /**
     * Row-level reports (Penjualan, Pembelian, Stok) — queued so a wide date
     * range never blocks the request; a database notification fires when done.
     */
    public function exportAsync(Request $request): JsonResponse
    {
        $request->validate([
            'type' => ['required', 'in:penjualan,pembelian,stok'],
            'format' => ['required', 'in:pdf,excel'],
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
        ]);

        $reportLog = ReportLog::create([
            'type' => $request->string('type'),
            'format' => $request->string('format'),
            'status' => 'processing',
            'requested_by' => Auth::id(),
        ]);

        ExportReportJob::dispatch(
            $reportLog->id,
            $request->string('type')->toString(),
            $request->string('format')->toString(),
            $request->string('from')->toString(),
            $request->string('to')->toString(),
        );

        return response()->json([
            'success' => true,
            'data' => $reportLog,
            'message' => 'Laporan sedang diproses. Anda akan mendapat notifikasi saat selesai.',
        ], 202);
    }

    public function download(ReportLog $report)
    {
        abort_unless($report->status === 'completed' && $report->file_path, 404);

        return Storage::disk('public')->download($report->file_path);
    }
}
