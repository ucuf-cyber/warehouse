<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BackupCompletedNotification extends Notification
{
    use Queueable;

    public function __construct(private bool $successful, private ?string $errorMessage = null) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => $this->successful
                ? 'Backup database harian berhasil dibuat.'
                : 'Backup database gagal: '.($this->errorMessage ?? 'Terjadi kesalahan.'),
            'url' => route('pengaturan.index'),
            'successful' => $this->successful,
        ];
    }
}
