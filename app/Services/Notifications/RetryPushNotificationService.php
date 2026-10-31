<?php

namespace App\Services\Notifications;

use App\Enums\PushNotificationStatus;
use App\Enums\RecipientStatus;
use App\Models\PushNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RetryPushNotificationService
{
    public function __construct(private readonly QueuePushNotificationService $queue) {}

    /**
     * Relanza los destinatarios pendientes y los que fallaron con error transitorio.
     * Sirve para envíos fallidos, marcados como enviados con pendientes o trabados en proceso.
     *
     * @param  bool  $force  Relanza aunque el envío figure en proceso con actividad reciente.
     */
    public function retry(PushNotification $notification, bool $force = false): PushNotification
    {
        $allowedStatuses = [
            PushNotificationStatus::Failed,
            PushNotificationStatus::Sent,
            PushNotificationStatus::Processing,
        ];

        if (! in_array($notification->status, $allowedStatuses, true)) {
            throw ValidationException::withMessages([
                'status' => 'Solo se puede reintentar un envío fallido, enviado con pendientes o trabado.',
            ]);
        }

        if (! $force && ! $notification->isResumable()) {
            throw ValidationException::withMessages([
                'status' => $notification->recipients()->deliverable()->exists()
                    ? 'El envío sigue procesándose. Esperá unos minutos antes de reintentarlo.'
                    : 'No hay destinatarios reintentables. Los tokens inválidos no se reintentan.',
            ]);
        }

        if (! $notification->recipients()->deliverable()->exists()) {
            throw ValidationException::withMessages([
                'status' => 'No hay destinatarios reintentables. Los tokens inválidos no se reintentan.',
            ]);
        }

        // Saca de la cola cualquier job viejo de este envío para no duplicar envíos.
        if ($notification->job_id) {
            DB::table('jobs')->where('id', $notification->job_id)->delete();
        }

        $notification->recipients()
            ->where('status', RecipientStatus::Failed->value)
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
