<?php

namespace Tests\Feature\Api;

use App\Jobs\SendPushNotificationJob;
use App\Models\Client;
use App\Models\DeviceUser;
use Database\Factories\FirebaseAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DeviceAndPushApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_user_upserts_and_update_token_changes_it(): void
    {
        [$client, $key] = $this->clientWithKey();

        $first = $this->withHeader('X-API-Key', $key)->postJson('/api/v1/users', [
            'external_user_id' => 'user-1',
            'firebase_token' => 'token-first-value-aaaa',
            'platform' => 'ios',
            'name' => 'Ana',
        ]);

        $first->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.external_user_id', 'user-1')
            ->assertJsonPath('data.token_hint', '***aaaa');

        $second = $this->withHeader('X-API-Key', $key)->postJson('/api/v1/users', [
            'external_user_id' => 'user-1',
            'firebase_token' => 'token-second-value-bbbb',
            'platform' => 'android',
        ]);

        $second->assertOk();
        $this->assertSame(1, DeviceUser::query()->count());
        $this->assertSame('token-second-value-bbbb', DeviceUser::query()->first()->firebase_token);

        $this->withHeader('Authorization', 'Bearer '.$key)
            ->putJson('/api/v1/users/user-1/token', [
                'firebase_token' => 'token-third-value-cccc',
            ])
            ->assertOk()
            ->assertJsonPath('data.platform', 'android');

        $this->withHeader('X-API-Key', $key)
            ->putJson('/api/v1/users/missing/token', ['firebase_token' => 'token-missing-value-dddd'])
            ->assertNotFound()
            ->assertJsonPath('success', false);
    }

    public function test_rejects_missing_invalid_and_inactive_keys(): void
    {
        $this->postJson('/api/v1/users', [])->assertUnauthorized();

        [$client, $key] = $this->clientWithKey();
        $this->withHeader('X-API-Key', 'nope')->postJson('/api/v1/users', [])->assertUnauthorized();

        $client->update(['is_active' => false]);
        $this->withHeader('X-API-Key', $key)->postJson('/api/v1/users', [
            'external_user_id' => 'user-1',
            'firebase_token' => 'token-inactive-value-eeee',
            'platform' => 'ios',
        ])->assertForbidden();
    }

    public function test_send_push_is_queued_and_can_be_queried(): void
    {
        Bus::fake();
        [$client, $key] = $this->clientWithKey(['firebase' => true]);
        DeviceUser::factory()->create([
            'client_id' => $client->id,
            'external_user_id' => 'user-1',
            'firebase_token' => 'token-queued-value-ffff',
        ]);

        $response = $this->withHeader('X-API-Key', $key)->postJson('/api/v1/pushes', [
            'external_user_ids' => ['user-1'],
            'title' => 'Hola',
            'body' => 'Mundo',
            'scheduled_at' => now()->addHour()->toIso8601String(),
        ]);

        $response->assertStatus(202)->assertJsonPath('data.status', 'scheduled');
        Bus::assertDispatched(SendPushNotificationJob::class);

        $id = $response->json('data.id');
        $this->withHeader('X-API-Key', $key)
            ->getJson('/api/v1/pushes/'.$id)
            ->assertOk()
            ->assertJsonPath('data.title', 'Hola');
    }

    public function test_rate_limit_uses_the_client_setting(): void
    {
        [$client, $key] = $this->clientWithKey(['rate_limit_per_minute' => 1]);

        $payload = [
            'external_user_id' => 'user-1',
            'firebase_token' => 'token-limit-value-gggg',
            'platform' => 'ios',
        ];

        $this->withHeader('X-API-Key', $key)->postJson('/api/v1/users', $payload)->assertCreated();
        $this->withHeader('X-API-Key', $key)->postJson('/api/v1/users', $payload)->assertStatus(429);
    }

    public function test_service_account_is_stored_encrypted(): void
    {
        $account = FirebaseAccount::make();
        $client = Client::factory()->withFirebase($account)->create();

        $raw = DB::table('clients')->where('id', $client->id)->value('firebase_service_account');

        $this->assertIsString($raw);
        $this->assertStringNotContainsString('PRIVATE KEY', $raw);
        $this->assertSame($account['client_email'], $client->fresh()->firebase_service_account['client_email']);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array{0: Client, 1: string}
     */
    private function clientWithKey(array $attributes = []): array
    {
        $key = 'pns_test_key_1234567890abcd';
        $firebase = (bool) ($attributes['firebase'] ?? false);
        unset($attributes['firebase']);

        $factory = Client::factory()->state([
            'api_key_hash' => hash('sha256', $key),
            'api_key_prefix' => substr($key, 0, 12),
            'api_key_last_four' => substr($key, -4),
        ]);

        if ($firebase) {
            $factory = $factory->withFirebase();
        }

        return [$factory->create($attributes), $key];
    }
}
