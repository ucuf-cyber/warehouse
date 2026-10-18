<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\FinanceTransaction;
use App\Models\Product;
use App\Models\Sale;
use App\Services\FinanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class FinanceServiceTest extends TestCase
{
    use RefreshDatabase;

    private FinanceService $financeService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->financeService = app(FinanceService::class);
    }

    public function test_cash_balance_is_income_minus_expense(): void
    {
        FinanceTransaction::factory()->create(['type' => 'income', 'amount' => 500000]);
        FinanceTransaction::factory()->create(['type' => 'income', 'amount' => 300000]);
        FinanceTransaction::factory()->create(['type' => 'expense', 'amount' => 200000]);

        $this->assertSame(600000, $this->financeService->cashBalance());
    }

    public function test_profit_loss_computes_omzet_hpp_and_net_profit(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'harga_modal' => 10000,
            'harga_jual' => 25000,
        ]);

        $sale = Sale::factory()->create([
            'status' => 'completed',
            'sale_date' => now(),
            'total_amount' => 50000,
        ]);
        $sale->items()->create([
            'product_id' => $product->id,
            'qty' => 2,
            'price' => 25000,
            'subtotal' => 50000,
        ]);

        FinanceTransaction::factory()->create([
            'type' => 'expense',
            'category' => 'Iklan',
            'amount' => 5000,
            'transaction_date' => now(),
        ]);

        $result = $this->financeService->profitLoss(now()->startOfDay(), now()->endOfDay());

        // Omzet: 2 * 25.000 = 50.000; HPP: 2 * 10.000 = 20.000; laba kotor = 30.000
        $this->assertSame(50000, $result['omzet']);
        $this->assertSame(20000, $result['hpp']);
        $this->assertSame(30000, $result['laba_kotor']);
        $this->assertSame(5000, $result['beban_operasional']);
        $this->assertSame(25000, $result['laba_bersih']);
    }

    public function test_profit_loss_excludes_transactions_outside_the_period(): void
    {
        FinanceTransaction::factory()->create([
            'type' => 'expense',
            'category' => 'Iklan',
            'amount' => 99999,
            'transaction_date' => Carbon::now()->subYear(),
        ]);

        $result = $this->financeService->profitLoss(now()->startOfMonth(), now()->endOfMonth());

        $this->assertSame(0, $result['beban_operasional']);
    }
}
