<?php

namespace Database\Factories;

use App\Enums\PushNotificationStatus;
use App\Enums\PushOrigin;
use App\Models\Client;
use App\Models\PushNotification;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PushNotification>
 */
class PushNotificationFactory extends Factory
{
    protected $model = PushNotification::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'created_by' => null,
            'origin' => PushOrigin::Api,
            'title' => fake()->sentence(3),
            'body' => fake()->sentence(),
            'image_url' => null,
            'data' => null,
            'scheduled_at' => null,
            'status' => PushNotificationStatus::Pending,
            'job_id' => null,
            'request_id' => null,
            'attempts' => 0,
            'last_error' => null,
        ];
    }
}
