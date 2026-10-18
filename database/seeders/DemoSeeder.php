<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\FinanceTransaction;
use App\Models\Product;
use App\Models\Rack;
use App\Models\Sale;
use App\Models\SubRack;
use App\Models\Supplier;
use Faker\Generator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Fills the app with realistic-looking data so every dashboard chart, table,
 * and report has something to show. Purchases are seeded before sales and
 * processed in date order so products never go negative — but purchases and
 * sales are otherwise two separate passes, not a strict interleaved ledger,
 * so a given sale isn't guaranteed to happen "after" the specific purchase
 * that stocked it. That's a fine trade-off for demo data; it would matter
 * for a real financial audit trail, which this isn't.
 */
class DemoSeeder extends Seeder
{
    private Generator $faker;

    /** @var array<int, int> product_id => running stock, used to compute qty_before/qty_after */
    private array $stock = [];

    private const EXPENSE_CATEGORIES = ['Packing', 'Ongkir', 'Admin Marketplace', 'Iklan', 'Lain-lain'];

    public function run(): void
    {
        $this->faker = fake('id_ID');

        activity()->disableLogging();

        $this->command?->getOutput()->writeln('Seeding categories, brands, suppliers, racks...');
        $categories = $this->seedCategories();
        $brands = $this->seedBrands();
        $suppliers = $this->seedSuppliers();
        $subRacks = $this->seedRacks();

        $this->command?->getOutput()->writeln('Seeding 100 products...');
        $products = $this->seedProducts($categories, $brands, $suppliers, $subRacks);

        foreach ($products as $product) {
            $this->stock[$product->id] = 0;
        }

        $this->command?->getOutput()->writeln('Seeding 300 purchases...');
        $this->seedPurchases($products, $suppliers);

        $this->command?->getOutput()->writeln('Seeding 500 sales...');
        $this->seedSales($products);

        $this->command?->getOutput()->writeln('Seeding 200 expense transactions...');
        $this->seedExpenses();

        $this->command?->getOutput()->writeln('Syncing product.current_stock cache...');
        $this->syncProductStockCache();

        activity()->enableLogging();
    }

    private function seedCategories(): Collection
    {
        $data = [
            ['name' => 'Skincare', 'prefix' => 'SKN', 'icon' => 'bi-droplet'],
            ['name' => 'Kosmetik', 'prefix' => 'KSM', 'icon' => 'bi-palette'],
            ['name' => 'Obat Herbal', 'prefix' => 'HRB', 'icon' => 'bi-flower1'],
            ['name' => 'Vitamin', 'prefix' => 'VIT', 'icon' => 'bi-capsule'],
            ['name' => 'Body Care', 'prefix' => 'BDC', 'icon' => 'bi-bag-heart'],
            ['name' => 'Hair Care', 'prefix' => 'HRC', 'icon' => 'bi-magic'],
            ['name' => 'Suplemen', 'prefix' => 'SPL', 'icon' => 'bi-capsule-pill'],
            ['name' => 'Perawatan Bayi', 'prefix' => 'BBY', 'icon' => 'bi-balloon-heart'],
            ['name' => 'Aromaterapi', 'prefix' => 'ARO', 'icon' => 'bi-fire'],
        ];

        return collect($data)->map(fn ($row) => Category::create($row));
    }

    private function seedBrands(): Collection
    {
        return collect(range(1, 15))->map(fn () => Brand::create([
            'name' => $this->faker->unique()->company(),
        ]));
    }

    private function seedSuppliers(): Collection
    {
        return collect(range(1, 20))->map(fn () => Supplier::create([
            'name' => $this->faker->unique()->company(),
            'contact_person' => $this->faker->name(),
            'phone' => $this->faker->numerify('08##########'),
            'email' => $this->faker->companyEmail(),
            'address' => $this->faker->address(),
            'lead_time_days' => $this->faker->numberBetween(2, 14),
        ]));
    }

    private function seedRacks(): Collection
    {
        $subRacks = collect();

        foreach (range(1, 5) as $r) {
            $rack = Rack::create(['code' => "R{$r}", 'name' => "Rak {$r}"]);

            foreach (['A', 'B', 'C', 'D'] as $letter) {
                $subRacks->push(SubRack::create([
                    'rack_id' => $rack->id,
                    'code' => "R{$r}-{$letter}",
                    'name' => "Rak {$r} Sub {$letter}",
                ]));
            }
        }

        return $subRacks;
    }

    private function productNameTemplates(): array
    {
        return [
            'Skincare' => ['Serum %s', 'Toner %s', 'Sabun Cuci Muka %s', 'Krim Wajah %s', 'Sunscreen %s', 'Micellar Water %s', 'Facial Wash %s'],
            'Kosmetik' => ['Lipstik %s', 'Bedak Tabur %s', 'BB Cream %s', 'Maskara %s', 'Eyeliner %s', 'Blush On %s'],
            'Obat Herbal' => ['Jamu %s', 'Kapsul Herbal %s', 'Minyak Herbal %s', 'Teh Herbal %s', 'Param %s'],
            'Vitamin' => ['Vitamin C %s', 'Vitamin E %s', 'Multivitamin %s', 'Vitamin D3 %s', 'Vitamin B Kompleks %s'],
            'Body Care' => ['Body Lotion %s', 'Body Scrub %s', 'Body Wash %s', 'Hand Cream %s', 'Body Butter %s'],
            'Hair Care' => ['Shampoo %s', 'Hair Tonic %s', 'Hair Mask %s', 'Serum Rambut %s', 'Vitamin Rambut %s'],
            'Suplemen' => ['Suplemen Kolagen %s', 'Suplemen Herbal %s', 'Fish Oil %s', 'Suplemen Kecantikan %s'],
            'Perawatan Bayi' => ['Baby Lotion %s', 'Baby Oil %s', 'Sabun Bayi %s', 'Bedak Bayi %s'],
            'Aromaterapi' => ['Essential Oil %s', 'Reed Diffuser %s', 'Lilin Aromaterapi %s', 'Roll On Aromaterapi %s'],
        ];
    }

    private function seedProducts(Collection $categories, Collection $brands, Collection $suppliers, Collection $subRacks): Collection
    {
        $templates = $this->productNameTemplates();
        $variants = ['30ml', '50ml', '60ml', '100ml', '100gr', '250gr', '10 Tablet', '30 Kapsul', '60 Kapsul'];

        $products = collect();

        foreach (range(1, 100) as $i) {
            $category = $categories->random();
            $template = $this->faker->randomElement($templates[$category->name]);
            $name = sprintf($template, ucfirst($this->faker->word()).' '.$this->faker->randomElement($variants));

            $hargaModal = $this->faker->numberBetween(8, 150) * 1000;
            $hargaJual = (int) round($hargaModal * $this->faker->randomFloat(2, 1.3, 2.2), -2);
            $hargaMarketplace = (int) round($hargaJual * $this->faker->randomFloat(2, 1.05, 1.25), -2);

            /** @var Product $product */
            $product = Product::create([
                'name' => $name,
                'unit' => $this->faker->randomElement(['pcs', 'box', 'botol', 'sachet']),
                'category_id' => $category->id,
                'brand_id' => $this->faker->boolean(80) ? $brands->random()->id : null,
                'supplier_id' => $suppliers->random()->id,
                'sub_rack_id' => $subRacks->random()->id,
                'harga_modal' => $hargaModal,
                'harga_jual' => $hargaJual,
                'harga_marketplace' => $hargaMarketplace,
                'berat' => $this->faker->numberBetween(20, 500),
                'status' => 'active',
            ]);

            // barcode/qr_code are auto-filled by ProductObserver; only sku needs setting here.
            $product->update(['sku' => $product->kode_barang]);

            $products->push($product);
        }

        return $products;
    }

    private function seedPurchases(Collection $products, Collection $suppliers): void
    {
        $purchaseRows = [];
        $itemRows = [];
        $paymentRows = [];
        $movementRows = [];
        $financeRows = [];
        $now = now();

        $dates = collect(range(1, 300))
            ->map(fn () => Carbon::now()->subDays($this->faker->numberBetween(0, 420)))
            ->sort()
            ->values();

        foreach ($dates as $i => $date) {
            $code = 'PO-'.$date->format('Ym').'-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT);
            $supplier = $suppliers->random();
            $status = $this->faker->randomElement([
                'received', 'received', 'received', 'received', 'received', 'received', 'received',
                'ordered', 'ordered',
                'draft',
                'cancelled',
            ]);

            $items = [];
            $total = 0;

            foreach (range(1, $this->faker->numberBetween(1, 4)) as $x) {
                $product = $products->random();
                $qty = $this->faker->numberBetween(5, 25);
                $price = (int) round($product->harga_modal * $this->faker->randomFloat(2, 0.95, 1.05), -1);
                $subtotal = $qty * $price;
                $total += $subtotal;

                $items[] = [
                    'product_id' => $product->id,
                    'qty' => $qty,
                    'price' => $price,
                    'subtotal' => $subtotal,
                ];
            }

            $isFullyPaid = $status === 'received' && $this->faker->boolean(70);
            $paidAmount = match (true) {
                $status !== 'received' => 0,
                $isFullyPaid => $total,
                default => (int) round($total * $this->faker->randomFloat(2, 0.2, 0.7), -2),
            };

            $purchaseId = DB::table('purchases')->insertGetId([
                'code' => $code,
                'supplier_id' => $supplier->id,
                'status' => $status,
                'purchase_date' => $date->toDateString(),
                'total_amount' => $total,
                'paid_amount' => $paidAmount,
                'notes' => null,
                'user_id' => 1,
                'created_at' => $date,
                'updated_at' => $date,
            ]);

            foreach ($items as $item) {
                $itemRows[] = $item + [
                    'purchase_id' => $purchaseId,
                    'created_at' => $date,
                    'updated_at' => $date,
                ];

                if ($status === 'received') {
                    $before = $this->stock[$item['product_id']];
                    $after = $before + $item['qty'];
                    $this->stock[$item['product_id']] = $after;

                    $movementRows[] = [
                        'product_id' => $item['product_id'],
                        'type' => 'in',
                        'qty' => $item['qty'],
                        'qty_before' => $before,
                        'qty_after' => $after,
                        'reference_type' => \App\Models\Purchase::class,
                        'reference_id' => $purchaseId,
                        'notes' => "Pembelian {$code}",
                        'user_id' => 1,
                        'created_at' => $date,
                        'updated_at' => $date,
                    ];
                }
            }

            if ($paidAmount > 0) {
                $paymentRows[] = [
                    'purchase_id' => $purchaseId,
                    'amount' => $paidAmount,
                    'payment_date' => $date->toDateString(),
                    'method' => $this->faker->randomElement(['transfer', 'transfer', 'cash']),
                    'notes' => null,
                    'created_at' => $date,
                    'updated_at' => $date,
                ];

                $financeRows[] = [
                    'type' => 'expense',
                    'category' => 'Pembelian',
                    'amount' => $paidAmount,
                    'transaction_date' => $date->toDateString(),
                    'description' => "Pembayaran {$code}",
                    'reference_type' => \App\Models\Purchase::class,
                    'reference_id' => $purchaseId,
                    'user_id' => 1,
                    'created_at' => $date,
                    'updated_at' => $date,
                ];
            }
        }

        collect($itemRows)->chunk(500)->each(fn ($chunk) => DB::table('purchase_items')->insert($chunk->all()));
        collect($paymentRows)->chunk(500)->each(fn ($chunk) => DB::table('purchase_payments')->insert($chunk->all()));
        collect($movementRows)->chunk(500)->each(fn ($chunk) => DB::table('stock_movements')->insert($chunk->all()));
        collect($financeRows)->chunk(500)->each(fn ($chunk) => DB::table('finance_transactions')->insert($chunk->all()));
    }

    private function seedSales(Collection $products): void
    {
        $itemRows = [];
        $movementRows = [];
        $financeRows = [];

        $dates = collect(range(1, 500))
            ->map(fn () => Carbon::now()->subDays($this->faker->numberBetween(0, 364)))
            ->sort()
            ->values();

        foreach ($dates as $i => $date) {
            $marketplace = $this->faker->randomElement(['manual', 'manual', 'manual', 'manual', 'shopee', 'shopee', 'shopee', 'tiktok', 'tiktok']);
            $status = $this->faker->randomElement([
                'completed', 'completed', 'completed', 'completed', 'completed', 'completed',
                'processing', 'processing',
                'cancelled',
            ]);

            $code = 'SO-'.$date->format('Ym').'-'.str_pad((string) ($i + 1), 5, '0', STR_PAD_LEFT);

            $items = [];
            $total = 0;

            foreach (range(1, $this->faker->numberBetween(1, 3)) as $x) {
                $product = $products->random();
                $available = $this->stock[$product->id];

                $qty = $this->faker->numberBetween(1, 8);

                if ($status === 'completed') {
                    $qty = min($qty, $available);

                    if ($qty <= 0) {
                        continue;
                    }
                }

                $price = $marketplace === 'manual' ? $product->harga_jual : $product->harga_marketplace;
                $subtotal = $qty * $price;
                $total += $subtotal;

                $items[] = [
                    'product_id' => $product->id,
                    'qty' => $qty,
                    'price' => $price,
                    'subtotal' => $subtotal,
                ];
            }

            if (empty($items)) {
                continue;
            }

            $shippingCost = $marketplace === 'manual' ? 0 : $this->faker->numberBetween(9000, 25000);
            $adminFee = $marketplace === 'manual' ? 0 : (int) round($total * 0.05, -2);

            $saleId = DB::table('sales')->insertGetId([
                'code' => $code,
                'marketplace' => $marketplace,
                'marketplace_order_id' => $marketplace === 'manual' ? null : strtoupper($this->faker->bothify('??##########')),
                'customer_name' => $this->faker->name(),
                'status' => $status,
                'sale_date' => $date->toDateString(),
                'total_amount' => $total + $shippingCost,
                'shipping_cost' => $shippingCost,
                'admin_fee' => $adminFee,
                'user_id' => 1,
                'created_at' => $date,
                'updated_at' => $date,
            ]);

            foreach ($items as $item) {
                $itemRows[] = $item + [
                    'sale_id' => $saleId,
                    'created_at' => $date,
                    'updated_at' => $date,
                ];

                if ($status === 'completed') {
                    $before = $this->stock[$item['product_id']];
                    $after = $before - $item['qty'];
                    $this->stock[$item['product_id']] = $after;

                    $movementRows[] = [
                        'product_id' => $item['product_id'],
                        'type' => 'out',
                        'qty' => -$item['qty'],
                        'qty_before' => $before,
                        'qty_after' => $after,
                        'reference_type' => \App\Models\Sale::class,
                        'reference_id' => $saleId,
                        'notes' => "Penjualan {$code}",
                        'user_id' => 1,
                        'created_at' => $date,
                        'updated_at' => $date,
                    ];
                }
            }

            if ($status === 'completed') {
                $financeRows[] = [
                    'type' => 'income',
                    'category' => 'Penjualan',
                    'amount' => $total + $shippingCost - $adminFee,
                    'transaction_date' => $date->toDateString(),
                    'description' => "Penjualan {$code}",
                    'reference_type' => \App\Models\Sale::class,
                    'reference_id' => $saleId,
                    'user_id' => 1,
                    'created_at' => $date,
                    'updated_at' => $date,
                ];

                if ($adminFee > 0) {
                    $financeRows[] = [
                        'type' => 'expense',
                        'category' => 'Admin Marketplace',
                        'amount' => $adminFee,
                        'transaction_date' => $date->toDateString(),
                        'description' => "Biaya admin {$code}",
                        'reference_type' => \App\Models\Sale::class,
                        'reference_id' => $saleId,
                        'user_id' => 1,
                        'created_at' => $date,
                        'updated_at' => $date,
                    ];
                }
            }
        }

        collect($itemRows)->chunk(500)->each(fn ($chunk) => DB::table('sale_items')->insert($chunk->all()));
        collect($movementRows)->chunk(500)->each(fn ($chunk) => DB::table('stock_movements')->insert($chunk->all()));
        collect($financeRows)->chunk(500)->each(fn ($chunk) => DB::table('finance_transactions')->insert($chunk->all()));
    }

    private function seedExpenses(): void
    {
        $rows = collect(range(1, 200))->map(function () {
            $date = Carbon::now()->subDays($this->faker->numberBetween(0, 364));

            return [
                'type' => 'expense',
                'category' => $this->faker->randomElement(self::EXPENSE_CATEGORIES),
                'amount' => $this->faker->numberBetween(20, 500) * 1000,
                'transaction_date' => $date->toDateString(),
                'description' => $this->faker->sentence(4),
                'user_id' => 1,
                'created_at' => $date,
                'updated_at' => $date,
            ];
        });

        $rows->chunk(500)->each(fn ($chunk) => DB::table('finance_transactions')->insert($chunk->all()));
    }

    private function syncProductStockCache(): void
    {
        foreach ($this->stock as $productId => $qty) {
            DB::table('products')->where('id', $productId)->update(['current_stock' => max(0, $qty)]);
        }
    }
}
