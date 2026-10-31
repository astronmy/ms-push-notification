<?php

namespace App\Console\Commands;

use App\Models\PushNotification;
use App\Services\Notifications\RetryPushNotificationService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;

#[Signature('push:resume {notification?* : IDs de envíos a reanudar (vacío = todos los que tengan pendientes y estén trabados)} {--force : Reanuda aunque figuren en proceso con actividad reciente}')]
#[Description('Vuelve a encolar los destinatarios pendientes de envíos que quedaron a medias')]
class ResumePushNotifications extends Command
{
    public function handle(RetryPushNotificationService $retry): int
    {
        $ids = $this->argument('notification');
        $force = (bool) $this->option('force');

        $notifications = PushNotification::query()
            ->when($ids !== [], fn ($query) => $query->whereKey($ids))
            ->whereIn('status', ['failed', 'sent', 'processing'])
            ->whereHas('recipients', fn ($query) => $query->deliverable())
            ->orderBy('id')
            ->get();

        if ($notifications->isEmpty()) {
            $this->info('No hay envíos con destinatarios pendientes.');

            return self::SUCCESS;
        }

        foreach ($notifications as $notification) {
            $pending = $notification->recipients()->deliverable()->count();

            try {
                $retry->retry($notification, $force);
                $this->info("Envío #{$notification->id}: {$pending} destinatarios encolados de nuevo.");
            } catch (ValidationException $exception) {
                $this->warn("Envío #{$notification->id}: ".collect($exception->errors())->flatten()->first());
            }
        }

        return self::SUCCESS;
    }
}
