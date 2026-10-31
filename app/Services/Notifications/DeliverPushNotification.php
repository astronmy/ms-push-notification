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

    /**
     * Procesa una tanda de destinatarios. Devuelve true si quedan más por enviar
     * (el job encadena otro), false si el envío terminó.
     */
    public function deliver(int $pushNotificationId, bool $continuation = false): bool
    {
        $notification = PushNotification::query()
            ->with('client')
            ->find($pushNotificationId);

        if (! $notification || $notification->status === PushNotificationStatus::Cancelled) {
            return false;
        }

        if ($notification->status === PushNotificationStatus::Sent) {
            return false;
        }

        $client = $notification->client;

        if (! $client || ! $client->is_active || ! $client->hasFirebase()) {
            $notification->update([
                'status' => PushNotificationStatus::Failed,
                'failed_at' => now(),
                'last_error' => 'El cliente no tiene Firebase configurado o está inactivo.',
            ]);

            return false;
        }

        $notification->forceFill([
            'status' => PushNotificationStatus::Processing,
            'processing_started_at' => $continuation ? ($notification->processing_started_at ?? now()) : now(),
            'attempts' => $continuation ? $notification->attempts : $notification->attempts + 1,
        ]);
        // Siempre actualiza updated_at: sirve para detectar envíos trabados.
        $notification->updated_at = now();
        $notification->save();

        $retryableFailure = false;
        $lastError = null;

        $recipients = $notification->recipients()
            ->deliverable()
            ->with('deviceUser')
            ->orderBy('id')
            ->limit(max(1, (int) config('push.delivery.chunk_size', 100)))
            ->get();

        // Tokens que ya recibieron este envío: no se vuelve a mandar al mismo dispositivo.
        $sentTokens = array_fill_keys(
            $notification->recipients()
                ->where('status', RecipientStatus::Sent->value)
                ->pluck('firebase_token')
                ->all(),
            true,
        );
        $attemptedTokens = [];

        foreach ($recipients as $recipient) {
            $token = (string) $recipient->firebase_token;

            if (isset($sentTokens[$token])) {
                $recipient->update([
                    'status' => RecipientStatus::Skipped,
                    'error_code' => 'duplicate_token',
                    'error_message' => 'Token repetido: este dispositivo ya recibió la notificación.',
                ]);

                continue;
            }

            // Duplicado dentro de la misma tanda cuyo primer intento no salió: queda para el reintento.
            if (isset($attemptedTokens[$token])) {
                continue;
            }

            $attemptedTokens[$token] = true;

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
                $sentTokens[$token] = true;
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

        if ($retryableFailure) {
            $notification->update([
                'status' => PushNotificationStatus::Processing,
                'last_error' => $lastError,
            ]);

            throw new TransientFcmException($lastError ?? 'Error transitorio de FCM.');
        }

        if ($notification->recipients()->deliverable()->exists()) {
            return true;
        }

        $anySent = $notification->recipients()
            ->where('status', RecipientStatus::Sent->value)
            ->exists();

        $notification->update([
            'status' => $anySent ? PushNotificationStatus::Sent : PushNotificationStatus::Failed,
            'sent_at' => $anySent ? now() : null,
            'failed_at' => $anySent ? null : now(),
            'last_error' => $anySent ? null : ($lastError ?? 'Ningún destinatario pudo recibir la notificación.'),
        ]);

        return false;
    }
}
