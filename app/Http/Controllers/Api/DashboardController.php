<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Services\FinanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct(private FinanceService $financeService) {}

    public function summary(): JsonResponse
    {
        $ttl = now()->addMinutes((int) config('bms.dashboard_cache_ttl', 5));

        $data = Cache::remember('dashboard:summary', $ttl, function () {
            $now = now();
            $startOfMonth = $now->copy()->startOfMonth();
            $startOfLastMonth = $now->copy()->subMonthNoOverflow()->startOfMonth();
            $endOfLastMonth = $now->copy()->subMonthNoOverflow()->endOfMonth();

            $thisMonth = $this->financeService->totals($startOfMonth, $now);
            $lastMonth = $this->financeService->totals($startOfLastMonth, $endOfLastMonth);

            $profitToday = $this->financeService->totals($now->copy()->startOfDay(), $now);
            $profitYesterday = $this->financeService->totals($now->copy()->subDay()->startOfDay(), $now->copy()->subDay()->endOfDay());

            $todayNet = $profitToday['income'] - $profitToday['expense'];
            $yesterdayNet = $profitYesterday['income'] - $profitYesterday['expense'];

            return [
                'cash_balance' => $this->financeService->cashBalance(),
                'profit_today' => $todayNet,
                'profit_today_change_pct' => $this->percentChange($yesterdayNet, $todayNet),
                'income_this_month' => $thisMonth['income'],
                'income_change_pct' => $this->percentChange($lastMonth['income'], $thisMonth['income']),
                'expense_this_month' => $thisMonth['expense'],
                'expense_change_pct' => $this->percentChange($lastMonth['expense'], $thisMonth['expense']),
                'products_below_min_stock' => Product::active()->belowMinimumStock()->count(),
                ...$this->dailySparklines(7),
            ];
        });

        return response()->json(['success' => true, 'data' => $data, 'message' => '']);
    }

    private function percentChange(int|float $previous, int|float $current): float
    {
        if ($previous == 0) {
            return $current == 0 ? 0.0 : 100.0;
        }

        return round((($current - $previous) / abs($previous)) * 100, 1);
    }

    /**
     * @return array{sparkline_cash: int[], sparkline_income: int[], sparkline_expense: int[]} last N days, oldest first.
     */
    private function dailySparklines(int $days): array
    {
        $rows = DB::table('finance_transactions')
            ->selectRaw('DATE(transaction_date) as d, type, SUM(amount) as total')
            ->where('transaction_date', '>=', now()->subDays($days - 1)->startOfDay())
            ->groupBy('d', 'type')
            ->get()
            ->groupBy('d');

        $cash = [];
        $income = [];
        $expense = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $rowsForDay = $rows->get($date, collect());
            $dayIncome = (int) ($rowsForDay->firstWhere('type', 'income')->total ?? 0);
            $dayExpense = (int) ($rowsForDay->firstWhere('type', 'expense')->total ?? 0);

            $income[] = $dayIncome;
            $expense[] = $dayExpense;
            $cash[] = $dayIncome - $dayExpense;
        }

        return ['sparkline_cash' => $cash, 'sparkline_income' => $income, 'sparkline_expense' => $expense];
    }

    public function stockDistribution(): JsonResponse
    {
        $ttl = now()->addMinutes((int) config('bms.dashboard_cache_ttl', 5));

        $data = Cache::remember('dashboard:stock-distribution', $ttl, function () {
            $rows = Category::query()
                ->withSum('products as stock_sum', 'current_stock')
                ->orderByDesc('stock_sum')
                ->get()
                ->map(fn (Category $c) => ['label' => $c->name, 'value' => (int) $c->stock_sum]);

            $top = $rows->take(5);
            $rest = $rows->slice(5)->sum('value');

            if ($rest > 0) {
                $top->push(['label' => 'Lainnya', 'value' => $rest]);
            }

            return $top->values();
        });

        return response()->json(['success' => true, 'data' => $data, 'message' => '']);
    }

    public function financeSummary(Request $request): JsonResponse
    {
        $year = $request->integer('year') ?: now()->year;
        $ttl = now()->addMinutes((int) config('bms.dashboard_cache_ttl', 5));

        $data = Cache::remember("dashboard:finance-summary:{$year}", $ttl, function () use ($year) {
            $modal = Purchase::query()
                ->where('status', 'received')
                ->whereYear('purchase_date', $year)
                ->selectRaw($this->monthExpr('purchase_date').' as m, SUM(total_amount) as total')
                ->groupBy('m')->pluck('total', 'm');

            $omzet = Sale::query()
                ->where('status', 'completed')
                ->whereYear('sale_date', $year)
                ->selectRaw($this->monthExpr('sale_date').' as m, SUM(total_amount) as total')
                ->groupBy('m')->pluck('total', 'm');

            $hpp = SaleItem::query()
                ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
                ->join('products', 'products.id', '=', 'sale_items.product_id')
                ->where('sales.status', 'completed')
                ->whereYear('sales.sale_date', $year)
                ->selectRaw($this->monthExpr('sales.sale_date').' as m, SUM(sale_items.qty * products.harga_modal) as total')
                ->groupBy('m')->pluck('total', 'm');

            $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

            return collect(range(1, 12))->map(function ($m) use ($modal, $omzet, $hpp, $months) {
                $omzetVal = (int) ($omzet[$m] ?? 0);
                $hppVal = (int) ($hpp[$m] ?? 0);

                return [
                    'month' => $months[$m - 1],
                    'modal' => (int) ($modal[$m] ?? 0),
                    'omzet' => $omzetVal,
                    'profit' => max(0, $omzetVal - $hppVal),
                ];
            });
        });

        return response()->json(['success' => true, 'data' => $data, 'message' => '']);
    }

    public function recentSales(): JsonResponse
    {
        $sales = Sale::query()
            ->latest('sale_date')->latest('id')
            ->take(10)
            ->get(['id', 'code', 'marketplace', 'status', 'total_amount', 'sale_date', 'created_at']);

        return response()->json(['success' => true, 'data' => $sales, 'message' => '']);
    }

    /**
     * MySQL's MONTH() has no ANSI equivalent — Postgres (used in production
     * on Vercel) needs EXTRACT instead, while local dev runs MySQL.
     */
    private function monthExpr(string $column): string
    {
        return DB::connection()->getDriverName() === 'pgsql'
            ? "CAST(EXTRACT(MONTH FROM {$column}) AS INTEGER)"
            : "MONTH({$column})";
    }
}
