<?php

namespace App\Notifications;

use App\Models\ReportLog;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReportReadyNotification extends Notification
{
    use Queueable;

    public function __construct(private ReportLog $report) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => "Laporan {$this->report->type} ({$this->report->format}) sudah siap diunduh.",
            'url' => route('laporan.download', $this->report),
            'report_id' => $this->report->id,
        ];
    }
}
