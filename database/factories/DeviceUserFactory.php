<?php

namespace Database\Factories;

use App\Enums\Platform;
use App\Models\Client;
use App\Models\DeviceUser;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<DeviceUser>
 */
class DeviceUserFactory extends Factory
{
    protected $model = DeviceUser::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'external_user_id' => (string) Str::uuid(),
            'firebase_token' => 'fcm-'.Str::random(32),
            'platform' => Platform::Android,
            'name' => fake()->name(),
            'meta' => null,
            'is_active' => true,
            'invalidated_at' => null,
            'last_registered_at' => now(),
        ];
    }
}
