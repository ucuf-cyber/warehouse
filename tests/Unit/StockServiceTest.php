<?php

namespace Tests\Unit;

use App\Exceptions\InsufficientStockException;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockServiceTest extends TestCase
{
    use RefreshDatabase;

    private StockService $stockService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stockService = app(StockService::class);
        $this->actingAs(User::factory()->create());
    }

    private function makeProduct(int $stock = 10): Product
    {
        $category = Category::factory()->create();

        return Product::factory()->create([
            'category_id' => $category->id,
            'current_stock' => $stock,
        ]);
    }

    public function test_stock_in_increases_current_stock_and_writes_a_movement(): void
    {
        $product = $this->makeProduct(stock: 10);

        $movement = $this->stockService->stockIn($product, 5, notes: 'Test in');

        $this->assertSame(15, $product->fresh()->current_stock);
        $this->assertSame('in', $movement->type);
        $this->assertSame(5, $movement->qty);
        $this->assertSame(10, $movement->qty_before);
        $this->assertSame(15, $movement->qty_after);
    }

    public function test_stock_out_decreases_current_stock(): void
    {
        $product = $this->makeProduct(stock: 10);

        $movement = $this->stockService->stockOut($product, 4);

        $this->assertSame(6, $product->fresh()->current_stock);
        $this->assertSame('out', $movement->type);
        $this->assertSame(-4, $movement->qty);
    }

    public function test_stock_out_throws_when_it_would_go_negative(): void
    {
        $product = $this->makeProduct(stock: 3);

        $this->expectException(InsufficientStockException::class);

        $this->stockService->stockOut($product, 10);

        $this->assertSame(3, $product->fresh()->current_stock);
    }

    public function test_adjust_to_writes_the_correct_signed_delta(): void
    {
        $product = $this->makeProduct(stock: 20);

        $movement = $this->stockService->adjustTo($product, 15, notes: 'Opname correction');

        $this->assertSame(15, $product->fresh()->current_stock);
        $this->assertSame('adjustment', $movement->type);
        $this->assertSame(-5, $movement->qty);
    }

    public function test_return_increases_stock_like_stock_in(): void
    {
        $product = $this->makeProduct(stock: 10);

        $movement = $this->stockService->return($product, 2, notes: 'Customer return');

        $this->assertSame(12, $product->fresh()->current_stock);
        $this->assertSame('return', $movement->type);
        $this->assertSame(2, $movement->qty);
    }
}
