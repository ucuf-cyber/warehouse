<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class CheckLowStockCommand extends Command
{
    protected $signature = 'bms:check-low-stock';

    protected $description = 'Notify the Owner about every active product currently at or below its minimum stock';

    public function handle(): int
    {
        $products = Product::active()->belowMinimumStock()->get();

        if ($products->isEmpty()) {
            $this->info('No products below minimum stock.');

            return self::SUCCESS;
        }

        $users = User::all();

        foreach ($products as $product) {
            Notification::send($users, new LowStockNotification($product));
        }

        $this->info("Sent low-stock notifications for {$products->count()} product(s).");

        return self::SUCCESS;
    }
}
