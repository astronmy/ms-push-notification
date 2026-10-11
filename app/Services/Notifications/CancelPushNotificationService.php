<?php

namespace App\Services\Notifications;

use App\Enums\PushNotificationStatus;
use App\Models\PushNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CancelPushNotificationService
{
    public function cancel(PushNotification $notification): PushNotification
    {
        if (! $notification->status->isCancellable()) {
            throw ValidationException::withMessages([
                'status' => 'Solo se puede cancelar un envío pendiente o programado.',
            ]);
        }

        if ($notification->job_id) {
            DB::table('jobs')
                ->where('id', $notification->job_id)
                ->whereNull('reserved_at')
                ->delete();

            if (DB::table('jobs')->where('id', $notification->job_id)->exists()) {
                throw ValidationException::withMessages([
                    'status' => 'El envío ya empezó a procesarse y no se puede cancelar.',
                ]);
            }
        }

        $notification->update([
            'status' => PushNotificationStatus::Cancelled,
            'cancelled_at' => now(),
            'job_id' => null,
        ]);

        return $notification;
    }
}
