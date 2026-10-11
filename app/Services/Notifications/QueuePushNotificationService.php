<?php

namespace App\Services\Notifications;

use App\Enums\PushNotificationStatus;
use App\Enums\PushOrigin;
use App\Enums\RecipientStatus;
use App\Jobs\SendPushNotificationJob;
use App\Models\Client;
use App\Models\DeviceUser;
use App\Models\PushNotification;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QueuePushNotificationService
{
    /**
     * @param  list<string>  $externalUserIds
     * @param  array<string, mixed>|null  $data
     */
    public function queue(
        Client $client,
        array $externalUserIds,
        string $title,
        string $body,
        ?string $imageUrl,
        ?array $data,
        ?CarbonInterface $scheduledAt,
        PushOrigin $origin,
        ?int $createdBy = null,
        ?string $requestId = null,
    ): PushNotification {
        $this->assertFirebase($client);
        $devices = $this->devices($client, $externalUserIds);
        $scheduled = $scheduledAt && $scheduledAt->isFuture() ? $scheduledAt : null;

        return DB::transaction(function () use ($client, $devices, $title, $body, $imageUrl, $data, $scheduled, $origin, $createdBy, $requestId) {
            $notification = PushNotification::query()->create([
                'client_id' => $client->id,
                'created_by' => $createdBy,
                'origin' => $origin,
                'title' => $title,
                'body' => $body,
                'image_url' => $imageUrl,
                'data' => $data,
                'scheduled_at' => $scheduled,
                'status' => $scheduled ? PushNotificationStatus::Scheduled : PushNotificationStatus::Pending,
                'request_id' => $requestId,
                'attempts' => 0,
            ]);

            foreach ($devices as $device) {
                $notification->recipients()->create([
                    'device_user_id' => $device->id,
                    'firebase_token' => $device->firebase_token,
                    'status' => RecipientStatus::Pending,
                ]);
            }

            $this->dispatch($notification);

            return $notification->fresh(['recipients.deviceUser', 'client']);
        });
    }

    /**
     * @param  list<string>  $externalUserIds
     * @param  array<string, mixed>|null  $data
     */
    public function update(
        PushNotification $notification,
        array $externalUserIds,
        string $title,
        string $body,
        ?string $imageUrl,
        ?array $data,
        ?CarbonInterface $scheduledAt,
    ): PushNotification {
        if (! $notification->status->isEditable()) {
            throw ValidationException::withMessages([
                'status' => 'Solo se puede editar un envío pendiente o programado.',
            ]);
        }

        $client = $notification->client;
        $this->assertFirebase($client);
        $devices = $this->devices($client, $externalUserIds);
        $scheduled = $scheduledAt && $scheduledAt->isFuture() ? $scheduledAt : null;

        return DB::transaction(function () use ($notification, $devices, $title, $body, $imageUrl, $data, $scheduled) {
            $this->deleteJobIfIdle($notification);

            $notification->update([
                'title' => $title,
                'body' => $body,
                'image_url' => $imageUrl,
                'data' => $data,
                'scheduled_at' => $scheduled,
                'status' => $scheduled ? PushNotificationStatus::Scheduled : PushNotificationStatus::Pending,
                'last_error' => null,
            ]);

            $notification->recipients()->delete();

            foreach ($devices as $device) {
                $notification->recipients()->create([
                    'device_user_id' => $device->id,
                    'firebase_token' => $device->firebase_token,
                    'status' => RecipientStatus::Pending,
                ]);
            }

            $this->dispatch($notification->fresh());

            return $notification->fresh(['recipients.deviceUser', 'client']);
        });
    }

    public function dispatch(PushNotification $notification): void
    {
        $job = new SendPushNotificationJob($notification->id);

        if ($notification->scheduled_at?->isFuture()) {
            $job->delay($notification->scheduled_at);
        }

        $jobId = Bus::dispatch($job);

        if (is_numeric($jobId)) {
            $notification->forceFill(['job_id' => (int) $jobId])->save();
        }
    }

    private function assertFirebase(Client $client): void
    {
        if (! $client->hasFirebase()) {
            throw ValidationException::withMessages([
                'client' => 'El cliente no tiene un proyecto Firebase configurado.',
            ]);
        }
    }

    /**
     * @param  list<string>  $externalUserIds
     * @return Collection<int, DeviceUser>
     */
    private function devices(Client $client, array $externalUserIds): Collection
    {
        $ids = array_values(array_unique($externalUserIds));

        $devices = DeviceUser::query()
            ->where('client_id', $client->id)
            ->whereIn('external_user_id', $ids)
            ->get();

        $found = $devices->pluck('external_user_id')->all();
        $missing = array_values(array_diff($ids, $found));

        if ($missing !== []) {
            throw ValidationException::withMessages([
                'external_user_ids' => 'No existen estos usuarios para el cliente: '.implode(', ', $missing).'.',
            ]);
        }

        $inactive = $devices->where('is_active', false)->pluck('external_user_id')->all();

        if ($inactive !== []) {
            throw ValidationException::withMessages([
                'external_user_ids' => 'Estos usuarios están inactivos: '.implode(', ', $inactive).'. Volvé a registrarlos.',
            ]);
        }

        return $devices;
    }

    private function deleteJobIfIdle(PushNotification $notification): void
    {
        if (! $notification->job_id) {
            return;
        }

        $deleted = DB::table('jobs')
            ->where('id', $notification->job_id)
            ->whereNull('reserved_at')
            ->delete();

        $stillThere = DB::table('jobs')->where('id', $notification->job_id)->exists();

        if ($deleted === 0 && $stillThere) {
            throw ValidationException::withMessages([
                'status' => 'El envío ya empezó a procesarse y no se puede modificar.',
            ]);
        }
    }
}
