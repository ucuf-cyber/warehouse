<?php

namespace App\Notifications;

use App\Models\ProductBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ExpiringBatchNotification extends Notification
{
    use Queueable;

    public function __construct(private ProductBatch $batch) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $product = $this->batch->product;

        return [
            'message' => "Batch {$this->batch->batch_number} ({$product->name}) kedaluwarsa pada {$this->batch->expired_date->translatedFormat('d M Y')}.",
            'url' => route('produk.index'),
            'product_id' => $product->id,
        ];
    }
}
