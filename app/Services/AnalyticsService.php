<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Rule-based (moving-average) analytics — no ML model, no extra
 * infrastructure. Accurate enough for UMKM-scale inventory decisions:
 * a 30-day daily-sales average is a fine predictor at this volume.
 */
class AnalyticsService
{
    private const LOOKBACK_DAYS = 30;

    /**
     * Average daily sales qty per product over the last 30 days, keyed by product_id.
     */
    private function dailySalesAverages(): Collection
    {
        return DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.status', 'completed')
            ->where('sales.sale_date', '>=', now()->subDays(self::LOOKBACK_DAYS)->toDateString())
            ->selectRaw('sale_items.product_id, SUM(sale_items.qty) / ? as avg_daily_qty', [self::LOOKBACK_DAYS])
            ->groupBy('sale_items.product_id')
            ->get()
            ->keyBy('product_id');
    }

    /**
     * Estimated days of stock left per active product, soonest-to-run-out first.
     * Products with no recent sales are excluded (nothing to extrapolate from).
     *
     * @return Collection<int, array{product: Product, avg_daily_qty: float, days_left: float}>
     */
    public function stockOutPrediction(): Collection
    {
        $averages = $this->dailySalesAverages();

        return Product::active()
            ->whereIn('id', $averages->keys())
            ->get()
            ->map(function (Product $product) use ($averages) {
                $avgDaily = round((float) $averages[$product->id]->avg_daily_qty, 2);
                $daysLeft = $avgDaily > 0 ? round($product->current_stock / $avgDaily, 1) : INF;

                return ['product' => $product, 'avg_daily_qty' => $avgDaily, 'days_left' => $daysLeft];
            })
            ->sortBy('days_left')
            ->values();
    }

    /**
     * Products that will run out before their supplier's lead time can
     * replace them — i.e. order now or stock out.
     *
     * @return Collection<int, array{product: Product, avg_daily_qty: float, days_left: float, lead_time_days: int, suggested_qty: int}>
     */
    public function restockRecommendations(): Collection
    {
        return $this->stockOutPrediction()
            ->filter(function ($row) {
                $leadTime = $row['product']->supplier?->lead_time_days ?? 7;

                return $row['days_left'] <= $leadTime;
            })
            ->map(function ($row) {
                $leadTime = $row['product']->supplier?->lead_time_days ?? 7;
                // Enough to cover the lead time plus a 14-day safety buffer.
                $suggestedQty = (int) ceil($row['avg_daily_qty'] * ($leadTime + 14));

                return $row + ['lead_time_days' => $leadTime, 'suggested_qty' => max($suggestedQty, 1)];
            })
            ->values();
    }

    /**
     * Active products with zero completed sales in the given window.
     *
     * @return Collection<int, Product>
     */
    public function slowMovingProducts(int $days = 30): Collection
    {
        $soldProductIds = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.status', 'completed')
            ->where('sales.sale_date', '>=', now()->subDays($days)->toDateString())
            ->distinct()
            ->pluck('sale_items.product_id');

        return Product::active()
            ->whereNotIn('id', $soldProductIds)
            ->orderByDesc('current_stock')
            ->get();
    }
}
