<?php

namespace App\Notifications;

use App\Models\Sale;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewSaleNotification extends Notification
{
    use Queueable;

    public function __construct(private Sale $sale) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => "Penjualan baru {$this->sale->code} — Rp ".number_format($this->sale->total_amount, 0, ',', '.'),
            'url' => route('penjualan.index'),
            'sale_id' => $this->sale->id,
        ];
    }
}
