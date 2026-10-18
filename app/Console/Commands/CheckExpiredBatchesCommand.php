<?php

namespace App\Console\Commands;

use App\Models\ProductBatch;
use App\Models\User;
use App\Notifications\ExpiringBatchNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class CheckExpiredBatchesCommand extends Command
{
    protected $signature = 'bms:check-expired-batches {--days=7 : Warn this many days before expiry}';

    protected $description = 'Notify the Owner about product batches expiring soon';

    public function handle(): int
    {
        $batches = ProductBatch::with('product')
            ->expiringWithin((int) $this->option('days'))
            ->where('qty', '>', 0)
            ->get();

        if ($batches->isEmpty()) {
            $this->info('No batches expiring soon.');

            return self::SUCCESS;
        }

        $users = User::all();

        foreach ($batches as $batch) {
            Notification::send($users, new ExpiringBatchNotification($batch));
        }

        $this->info("Sent expiry notifications for {$batches->count()} batch(es).");

        return self::SUCCESS;
    }
}
