<?php

namespace App\Services\Notifications;

use App\Enums\PushNotificationStatus;
use App\Enums\RecipientStatus;
use App\Models\PushNotification;
use Illuminate\Validation\ValidationException;

class RetryPushNotificationService
{
    public function __construct(private readonly QueuePushNotificationService $queue) {}

    public function retry(PushNotification $notification): PushNotification
    {
        if ($notification->status !== PushNotificationStatus::Failed) {
            throw ValidationException::withMessages([
                'status' => 'Solo se puede reintentar un envío fallido.',
            ]);
        }

        $retryable = $notification->recipients()
            ->where('status', RecipientStatus::Failed)
            ->where('error_code', 'transient')
            ->exists();

        $pending = $notification->recipients()
            ->where('status', RecipientStatus::Pending)
            ->exists();

        if (! $retryable && ! $pending) {
            throw ValidationException::withMessages([
                'status' => 'No hay destinatarios reintentables. Los tokens inválidos no se reintentan.',
            ]);
        }

        $notification->recipients()
            ->where('status', RecipientStatus::Failed)
            ->where('error_code', 'transient')
            ->update([
                'status' => RecipientStatus::Pending->value,
                'error_code' => null,
                'error_message' => null,
            ]);

        $notification->update([
            'status' => PushNotificationStatus::Pending,
            'scheduled_at' => null,
            'last_error' => null,
            'failed_at' => null,
            'job_id' => null,
        ]);

        $this->queue->dispatch($notification->fresh());

        return $notification->fresh(['recipients.deviceUser', 'client']);
    }
}
