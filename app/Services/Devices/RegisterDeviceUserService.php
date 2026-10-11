<?php

namespace App\Services\Devices;

use App\Enums\Platform;
use App\Models\Client;
use App\Models\DeviceUser;

class RegisterDeviceUserService
{
    /**
     * @param  array{external_user_id: string, firebase_token: string, platform: string, name?: string|null, meta?: array<string, mixed>|null}  $data
     */
    public function upsert(Client $client, array $data): DeviceUser
    {
        $device = DeviceUser::withTrashed()->firstOrNew([
            'client_id' => $client->id,
            'external_user_id' => $data['external_user_id'],
        ]);

        if ($device->trashed()) {
            $device->restore();
        }

        $device->fill([
            'firebase_token' => $data['firebase_token'],
            'platform' => Platform::from($data['platform']),
            'is_active' => true,
            'invalidated_at' => null,
            'last_registered_at' => now(),
        ]);

        if (array_key_exists('name', $data)) {
            $device->name = $data['name'];
        }

        if (array_key_exists('meta', $data)) {
            $device->meta = $data['meta'];
        }

        $device->save();

        return $device;
    }
}
