<?php

namespace App\Services\Notifications;

use App\Enums\PushNotificationStatus;
use App\Enums\RecipientStatus;
use App\Exceptions\TransientFcmException;
use App\Models\PushNotification;
use App\Services\Firebase\FcmClient;

class DeliverPushNotification
{
    public function __construct(private readonly FcmClient $fcm) {}

    public function deliver(int $pushNotificationId): void
    {
        $notification = PushNotification::query()
            ->with(['client', 'recipients.deviceUser'])
            ->find($pushNotificationId);

        if (! $notification || $notification->status === PushNotificationStatus::Cancelled) {
            return;
        }

        if ($notification->status === PushNotificationStatus::Sent) {
            return;
        }

        $client = $notification->client;

        if (! $client || ! $client->is_active || ! $client->hasFirebase()) {
            $notification->update([
                'status' => PushNotificationStatus::Failed,
                'failed_at' => now(),
                'last_error' => 'El cliente no tiene Firebase configurado o está inactivo.',
            ]);

            return;
        }

        $notification->update([
            'status' => PushNotificationStatus::Processing,
            'processing_started_at' => now(),
            'attempts' => $notification->attempts + 1,
        ]);

        $retryableFailure = false;
        $lastError = null;

        $recipients = $notification->recipients->filter(function ($recipient) {
            if ($recipient->status === RecipientStatus::Pending) {
                return true;
            }

            return $recipient->status === RecipientStatus::Failed
                && $recipient->error_code === 'transient';
        });

        foreach ($recipients as $recipient) {
            if (! $recipient->deviceUser) {
                $recipient->update([
                    'status' => RecipientStatus::Failed,
                    'error_code' => 'permanent',
                    'error_message' => 'El dispositivo ya no existe.',
                ]);

                continue;
            }

            try {
                $result = $this->fcm->send($client, $notification, $recipient);
            } catch (TransientFcmException $exception) {
                $recipient->update([
                    'status' => RecipientStatus::Failed,
                    'error_code' => 'transient',
                    'error_message' => $exception->getMessage(),
                ]);
                $retryableFailure = true;
                $lastError = $exception->getMessage();

                continue;
            }

            if ($result->successful) {
                $recipient->update([
                    'status' => RecipientStatus::Sent,
                    'error_code' => null,
                    'error_message' => null,
                    'sent_at' => now(),
                ]);

                continue;
            }

            if ($result->invalidToken) {
                $recipient->update([
                    'status' => RecipientStatus::Failed,
                    'error_code' => 'invalid_token',
                    'error_message' => $result->message,
                ]);
                $recipient->deviceUser->update([
                    'is_active' => false,
                    'invalidated_at' => now(),
                ]);

                continue;
            }

            $code = $result->retryable ? 'transient' : 'permanent';
            $recipient->update([
                'status' => RecipientStatus::Failed,
                'error_code' => $code,
                'error_message' => $result->message,
            ]);

            if ($result->retryable) {
                $retryableFailure = true;
                $lastError = $result->message ?? 'Error transitorio de FCM.';
            }
        }

        $notification->load('recipients');
        $anySent = $notification->recipients->contains(
            fn ($recipient) => $recipient->status === RecipientStatus::Sent
        );

        if ($retryableFailure) {
            $notification->update([
                'status' => PushNotificationStatus::Processing,
                'last_error' => $lastError,
            ]);

            throw new TransientFcmException($lastError ?? 'Error transitorio de FCM.');
        }

        $notification->update([
            'status' => $anySent ? PushNotificationStatus::Sent : PushNotificationStatus::Failed,
            'sent_at' => $anySent ? now() : null,
            'failed_at' => $anySent ? null : now(),
            'last_error' => $anySent ? null : ($lastError ?? 'Ningún destinatario pudo recibir la notificación.'),
        ]);
    }
}
