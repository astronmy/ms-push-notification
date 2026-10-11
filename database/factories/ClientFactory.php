<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    protected $model = Client::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $plain = 'pns_'.Str::random(40);

        return [
            'name' => fake()->company(),
            'is_active' => true,
            'contact_name' => fake()->name(),
            'contact_email' => fake()->safeEmail(),
            'notes' => null,
            'api_key_hash' => hash('sha256', $plain),
            'api_key_prefix' => substr($plain, 0, 12),
            'api_key_last_four' => substr($plain, -4),
            'firebase_project_id' => null,
            'firebase_service_account' => null,
            'rate_limit_per_minute' => null,
        ];
    }

    /**
     * @param  array<string, mixed>|null  $account
     */
    public function withFirebase(?array $account = null): static
    {
        return $this->state(function () use ($account) {
            $account ??= FirebaseAccount::make();

            return [
                'firebase_project_id' => $account['project_id'],
                'firebase_service_account' => $account,
            ];
        });
    }
}
