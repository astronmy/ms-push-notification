<?php

namespace App\Services\Devices;

use App\Enums\Platform;
use App\Models\Client;
use App\Models\DeviceUser;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UpdateDeviceTokenService
{
    /**
     * @param  array{firebase_token: string, platform?: string|null}  $data
     */
    public function update(Client $client, string $externalUserId, array $data): DeviceUser
    {
        $device = DeviceUser::query()
            ->where('client_id', $client->id)
            ->where('external_user_id', $externalUserId)
            ->first();

        if (! $device) {
            throw (new ModelNotFoundException)->setModel(DeviceUser::class);
        }

        $device->firebase_token = $data['firebase_token'];
        $device->is_active = true;
        $device->invalidated_at = null;
        $device->last_registered_at = now();

        if (! empty($data['platform'])) {
            $device->platform = Platform::from($data['platform']);
        }

        $device->save();

        return $device;
    }
}
