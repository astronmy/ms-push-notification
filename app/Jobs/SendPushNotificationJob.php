<?php

namespace App\Jobs;

use App\Enums\PushNotificationStatus;
use App\Models\PushNotification;
use App\Services\Notifications\DeliverPushNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class SendPushNotificationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /**
     * @var list<int>
     */
    public array $backoff = [15, 60, 180];

    public function __construct(public int $pushNotificationId) {}

    public function handle(DeliverPushNotification $deliver): void
    {
        $deliver->deliver($this->pushNotificationId);
    }

    public function failed(?Throwable $exception): void
    {
        $notification = PushNotification::query()->with('recipients')->find($this->pushNotificationId);

        if (! $notification || $notification->status === PushNotificationStatus::Cancelled) {
            return;
        }

        $anySent = $notification->recipients->contains(
            fn ($recipient) => $recipient->status->value === 'sent'
        );

        $notification->update([
            'status' => $anySent ? PushNotificationStatus::Sent : PushNotificationStatus::Failed,
            'sent_at' => $anySent ? ($notification->sent_at ?? now()) : null,
            'failed_at' => $anySent ? null : now(),
            'last_error' => $exception?->getMessage(),
        ]);
    }
}
