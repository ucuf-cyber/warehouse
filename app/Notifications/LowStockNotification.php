<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification
{
    use Queueable;

    public function __construct(private Product $product) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => "Stok {$this->product->name} tinggal {$this->product->current_stock} {$this->product->unit} (minimum ".Product::MIN_STOCK_THRESHOLD.').',
            'url' => route('produk.index', ['low_stock' => 1]),
            'product_id' => $this->product->id,
        ];
    }
}
