<?php

namespace App\Jobs;

use App\Enums\PushNotificationStatus;
use App\Models\PushNotification;
use App\Services\Notifications\DeliverPushNotification;
use App\Services\Notifications\QueuePushNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Throwable;

/**
 * Envía una tanda de destinatarios (config push.delivery.chunk_size) y, si quedan
 * pendientes, encadena otro job. Así ningún job se acerca al timeout del worker.
 */
class SendPushNotificationJob implements ShouldQueue
{
    use Queueable;

    /**
     * Intentos totales (incluye los release por solapamiento).
     */
    public int $tries = 10;

    /**
     * Errores reales (excepciones) antes de marcarlo como fallido.
     */
    public int $maxExceptions = 3;

    /**
     * Debe quedar por debajo de retry_after de la conexión (90s por defecto).
     */
    public int $timeout = 75;

    /**
     * @var list<int>
     */
    public array $backoff = [15, 60, 180];

    public function __construct(
        public int $pushNotificationId,
        public bool $continuation = false,
    ) {}

    /**
     * Evita que dos workers procesen el mismo envío a la vez (envíos duplicados).
     *
     * @return list<object>
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping((string) $this->pushNotificationId))
                ->releaseAfter(10)
                ->expireAfter($this->timeout + 15),
        ];
    }

    public function handle(DeliverPushNotification $deliver): void
    {
        $hasMore = $deliver->deliver($this->pushNotificationId, $this->continuation);

        if ($hasMore) {
            app(QueuePushNotificationService::class)->dispatchContinuation($this->pushNotificationId);
        }
    }

    public function failed(?Throwable $exception): void
    {
        $notification = PushNotification::query()->find($this->pushNotificationId);

        if (! $notification || $notification->status === PushNotificationStatus::Cancelled) {
            return;
        }

        $remaining = $notification->recipients()->deliverable()->count();
        $anySent = $notification->recipients()->where('status', 'sent')->exists();

        // Si quedaron destinatarios sin enviar se marca como fallido para poder reintentarlo.
        $finished = $remaining === 0 && $anySent;

        $notification->update([
            'status' => $finished ? PushNotificationStatus::Sent : PushNotificationStatus::Failed,
            'sent_at' => $anySent ? ($notification->sent_at ?? now()) : null,
            'failed_at' => $finished ? null : now(),
            'last_error' => $remaining > 0
                ? "Quedaron {$remaining} destinatarios sin enviar. ".($exception?->getMessage() ?? '')
                : $exception?->getMessage(),
        ]);
    }
}
