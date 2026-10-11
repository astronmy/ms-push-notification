<?php

namespace Tests\Feature\Api;

use App\Models\ApiLog;
use App\Models\Client;
use App\Models\DeviceUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiLogMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_log_stores_the_call_and_masks_the_token(): void
    {
        $key = 'pns_log_key_1234567890abcd';
        $token = 'super-secret-firebase-token-zzzz';

        Client::factory()->create([
            'api_key_hash' => hash('sha256', $key),
            'api_key_prefix' => substr($key, 0, 12),
            'api_key_last_four' => substr($key, -4),
        ]);

        $this->withHeader('X-API-Key', $key)->postJson('/api/v1/users', [
            'external_user_id' => 'user-1',
            'firebase_token' => $token,
            'platform' => 'android',
        ])->assertCreated();

        $log = ApiLog::query()->first();

        $this->assertNotNull($log);
        $this->assertSame('registerUser', $log->action);
        $this->assertSame('POST', $log->method);
        $this->assertSame(201, $log->status);
        $this->assertNotNull($log->client_id);

        $encoded = json_encode($log->payload).json_encode($log->response);
        $this->assertStringNotContainsString($token, $encoded);
        $this->assertStringNotContainsString($key, $encoded);
        $this->assertStringContainsString('***zzzz', $encoded);
        $this->assertSame(1, DeviceUser::query()->count());
    }

    public function test_failed_auth_is_logged_without_a_client(): void
    {
        $this->withHeader('X-API-Key', 'missing-key')->postJson('/api/v1/users', [])->assertUnauthorized();

        $log = ApiLog::query()->first();
        $this->assertNotNull($log);
        $this->assertNull($log->client_id);
        $this->assertSame(401, $log->status);
        $this->assertStringNotContainsString('missing-key', json_encode($log->payload));
    }
}
