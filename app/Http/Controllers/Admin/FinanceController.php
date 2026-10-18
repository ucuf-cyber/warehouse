<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Keuangan\FinanceTransactionRequest;
use App\Models\FinanceTransaction;
use App\Services\FinanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class FinanceController extends Controller
{
    public function __construct(private FinanceService $financeService) {}

    public function index(): View
    {
        return view('admin.keuangan.index', [
            'expenseCategories' => FinanceTransaction::EXPENSE_CATEGORIES,
        ]);
    }

    public function summary(): JsonResponse
    {
        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();
        $lastMonth = $now->copy()->subMonthNoOverflow();

        $thisMonth = $this->financeService->totals($startOfMonth, $now);
        $lastMonthTotals = $this->financeService->totals($lastMonth->copy()->startOfMonth(), $lastMonth->copy()->endOfMonth());

        return response()->json([
            'success' => true,
            'data' => [
                'cash_balance' => $this->financeService->cashBalance(),
                'income_this_month' => $thisMonth['income'],
                'expense_this_month' => $thisMonth['expense'],
                'income_last_month' => $lastMonthTotals['income'],
                'expense_last_month' => $lastMonthTotals['expense'],
            ],
            'message' => '',
        ]);
    }

    public function data(Request $request)
    {
        $query = FinanceTransaction::query()->with('user:id,name')->orderBy('transaction_date', 'desc');

        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }

        if ($request->filled('category')) {
            $query->where('category', $request->string('category'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('transaction_date', '>=', $request->date('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('transaction_date', '<=', $request->date('date_to'));
        }

        return DataTables::eloquent($query)
            ->addColumn('user_name', fn (FinanceTransaction $t) => $t->user->name ?? 'System')
            ->addColumn('is_editable', fn (FinanceTransaction $t) => is_null($t->reference_type))
            ->toJson();
    }

    public function store(FinanceTransactionRequest $request): JsonResponse
    {
        $transaction = FinanceTransaction::create($request->validated() + ['user_id' => Auth::id()]);

        return response()->json([
            'success' => true,
            'data' => $transaction,
            'message' => 'Transaksi berhasil dicatat.',
        ], 201);
    }

    public function destroy(FinanceTransaction $keuangan): JsonResponse
    {
        if ($keuangan->reference_type) {
            return response()->json([
                'success' => false, 'data' => null,
                'message' => 'Transaksi otomatis (dari penjualan/pembelian) tidak dapat dihapus manual.',
            ], 422);
        }

        $keuangan->delete();

        return response()->json(['success' => true, 'data' => null, 'message' => 'Transaksi berhasil dihapus.']);
    }

    public function profitLoss(Request $request): JsonResponse
    {
        [$from, $to] = $this->resolvePeriod($request);

        return response()->json(['success' => true, 'data' => $this->financeService->profitLoss($from, $to), 'message' => '']);
    }

    public function cashflow(Request $request): JsonResponse
    {
        [$from, $to] = $this->resolvePeriod($request, 6);

        return response()->json(['success' => true, 'data' => $this->financeService->cashflow($from, $to), 'message' => '']);
    }

    public function balanceSheet(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $this->financeService->balanceSheet(), 'message' => '']);
    }

    private function resolvePeriod(Request $request, int $defaultMonthsBack = 1): array
    {
        $to = $request->filled('to') ? Carbon::parse($request->string('to')) : now();
        $from = $request->filled('from') ? Carbon::parse($request->string('from')) : $to->copy()->subMonthsNoOverflow($defaultMonthsBack)->startOfMonth();

        return [$from, $to];
    }
}
