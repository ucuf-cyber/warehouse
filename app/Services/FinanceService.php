<?php

namespace App\Services;

use App\Models\FinanceTransaction;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\SaleItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * All the aggregate finance numbers the Keuangan and Laporan pages need.
 * Nothing here writes data — it only reads finance_transactions, sales, and
 * purchases to compute summaries.
 */
class FinanceService
{
    public function cashBalance(): int
    {
        $income = FinanceTransaction::income()->sum('amount');
        $expense = FinanceTransaction::expense()->sum('amount');

        return $income - $expense;
    }

    public function totals(Carbon $from, Carbon $to): array
    {
        $query = FinanceTransaction::whereBetween('transaction_date', [$from->toDateString(), $to->toDateString()]);

        return [
            'income' => (clone $query)->income()->sum('amount'),
            'expense' => (clone $query)->expense()->sum('amount'),
        ];
    }

    /**
     * @return array{omzet:int, hpp:int, laba_kotor:int, beban_operasional:int, laba_bersih:int}
     */
    public function profitLoss(Carbon $from, Carbon $to): array
    {
        $omzet = (int) SaleItem::query()
            ->whereHas('sale', fn ($q) => $q->where('status', 'completed')->whereBetween('sale_date', [$from->toDateString(), $to->toDateString()]))
            ->sum('subtotal');

        $hpp = (int) SaleItem::query()
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->whereHas('sale', fn ($q) => $q->where('status', 'completed')->whereBetween('sale_date', [$from->toDateString(), $to->toDateString()]))
            ->sum(\Illuminate\Support\Facades\DB::raw('sale_items.qty * products.harga_modal'));

        $labaKotor = $omzet - $hpp;

        $bebanOperasional = (int) FinanceTransaction::expense()
            ->whereBetween('transaction_date', [$from->toDateString(), $to->toDateString()])
            ->where('category', '!=', 'Pembelian')
            ->sum('amount');

        return [
            'omzet' => $omzet,
            'hpp' => $hpp,
            'laba_kotor' => $labaKotor,
            'beban_operasional' => $bebanOperasional,
            'laba_bersih' => $labaKotor - $bebanOperasional,
        ];
    }

    /**
     * @return Collection<int, array{period:string, income:int, expense:int, balance:int}>
     */
    public function cashflow(Carbon $from, Carbon $to): Collection
    {
        $periodExpr = DB::connection()->getDriverName() === 'pgsql'
            ? "TO_CHAR(transaction_date, 'YYYY-MM')"
            : "DATE_FORMAT(transaction_date, '%Y-%m')";

        $rows = FinanceTransaction::query()
            ->selectRaw("{$periodExpr} as period, type, SUM(amount) as total")
            ->whereBetween('transaction_date', [$from->toDateString(), $to->toDateString()])
            ->groupBy('period', 'type')
            ->get();

        $periods = collect();
        $cursor = $from->copy()->startOfMonth();

        while ($cursor->lte($to)) {
            $periods->put($cursor->format('Y-m'), ['period' => $cursor->format('Y-m'), 'income' => 0, 'expense' => 0]);
            $cursor->addMonth();
        }

        foreach ($rows as $row) {
            if (! $periods->has($row->period)) {
                continue;
            }

            $period = $periods->get($row->period);
            $period[$row->type] = (int) $row->total;
            $periods->put($row->period, $period);
        }

        $balance = 0;

        return $periods->map(function ($row) use (&$balance) {
            $balance += $row['income'] - $row['expense'];
            $row['balance'] = $balance;

            return $row;
        })->values();
    }

    /**
     * @return array{aset:array, kewajiban:array, modal:int}
     */
    public function balanceSheet(): array
    {
        $kas = $this->cashBalance();

        $nilaiPersediaan = (int) Product::query()->sum(\Illuminate\Support\Facades\DB::raw('current_stock * harga_modal'));

        $hutangSupplier = (int) Purchase::query()
            ->where('status', 'received')
            ->sum(\Illuminate\Support\Facades\DB::raw('total_amount - paid_amount'));

        $totalAset = $kas + $nilaiPersediaan;

        return [
            'aset' => [
                'kas' => $kas,
                'persediaan' => $nilaiPersediaan,
                'total' => $totalAset,
            ],
            'kewajiban' => [
                'hutang_supplier' => $hutangSupplier,
                'total' => $hutangSupplier,
            ],
            'modal' => $totalAset - $hutangSupplier,
        ];
    }
}
