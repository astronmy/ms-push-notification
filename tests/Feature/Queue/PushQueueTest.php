<?php

namespace Tests\Feature\Queue;

use App\Enums\PushNotificationStatus;
use App\Enums\PushOrigin;
use App\Enums\RecipientStatus;
use App\Exceptions\TransientFcmException;
use App\Jobs\SendPushNotificationJob;
use App\Models\Client;
use App\Models\DeviceUser;
use App\Models\FirebaseLog;
use App\Models\FirebaseRequest;
use App\Models\PushNotification;
use App\Services\Notifications\CancelPushNotificationService;
use App\Services\Notifications\DeliverPushNotification;
use App\Services\Notifications\QueuePushNotificationService;
use App\Services\Notifications\RetryPushNotificationService;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PushQueueTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_sends_and_writes_firebase_logs_with_a_masked_token(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'oauth2.googleapis.com/*' => Http::response(['access_token' => 'ya29.test-token', 'expires_in' => 3600]),
            'fcm.googleapis.com/*' => Http::response(['name' => 'projects/demo-project/messages/1']),
        ]);

        $notification = $this->queuedNotification();
        $token = $notification->recipients->first()->firebase_token;

        (new SendPushNotificationJob($notification->id))->handle(app(DeliverPushNotification::class));

        $notification->refresh();
        $this->assertSame(PushNotificationStatus::Sent, $notification->status);
        $this->assertSame(1, FirebaseRequest::query()->where('action', 'send')->count());
        $this->assertSame(1, FirebaseLog::query()->where('action', 'send')->count());
        $this->assertStringNotContainsString($token, json_encode(FirebaseRequest::query()->where('action', 'send')->first()->payload));
        $this->assertStringNotContainsString('ya29.test-token', json_encode(FirebaseLog::query()->where('action', 'oauthToken')->first()->response));
    }

    public function test_invalid_token_is_deactivated_and_not_retried(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'oauth2.googleapis.com/*' => Http::response(['access_token' => 'ya29.test-token', 'expires_in' => 3600]),
            'fcm.googleapis.com/*' => Http::response([
                'error' => [
                    'code' => 404,
                    'message' => 'Requested entity was not found.',
                    'status' => 'NOT_FOUND',
                    'details' => [['errorCode' => 'UNREGISTERED']],
                ],
            ], 404),
        ]);

        $notification = $this->queuedNotification();
        $device = $notification->recipients->first()->deviceUser;

        (new SendPushNotificationJob($notification->id))->handle(app(DeliverPushNotification::class));

        $this->assertFalse($device->fresh()->is_active);
        $this->assertSame(PushNotificationStatus::Failed, $notification->fresh()->status);
        $this->assertSame('invalid_token', $notification->recipients()->first()->error_code);
        $this->assertSame(1, $notification->fresh()->attempts);
    }

    public function test_transient_failure_throws_and_can_be_retried_from_the_backoffice_service(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'oauth2.googleapis.com/*' => Http::response(['access_token' => 'ya29.test-token', 'expires_in' => 3600]),
            'fcm.googleapis.com/*' => Http::response(['error' => ['message' => 'unavailable', 'status' => 'UNAVAILABLE']], 503),
        ]);

        $notification = $this->queuedNotification();

        try {
            (new SendPushNotificationJob($notification->id))->handle(app(DeliverPushNotification::class));
            $this->fail('Se esperaba un error transitorio.');
        } catch (TransientFcmException) {
            $notification->refresh();
            $this->assertSame(RecipientStatus::Failed, $notification->recipients()->first()->status);
            $this->assertSame('transient', $notification->recipients()->first()->error_code);
        }

        config(['queue.default' => 'database']);
        $notification->update(['status' => PushNotificationStatus::Failed, 'failed_at' => now()]);

        app(RetryPushNotificationService::class)->retry($notification->fresh());

        $this->assertSame(PushNotificationStatus::Pending, $notification->fresh()->status);
        $this->assertSame(RecipientStatus::Pending, $notification->recipients()->first()->status);
        $this->assertDatabaseCount('jobs', 1);
    }

    public function test_cancel_removes_the_database_job_and_the_worker_does_not_send(): void
    {
        Http::preventStrayRequests();
        Http::fake();

        $notification = $this->queuedNotification(now()->addDay(), keepJob: true);

        $this->assertNotNull($notification->job_id);
        $this->assertDatabaseCount('jobs', 1);
        $this->assertSame(PushNotificationStatus::Scheduled, $notification->status);

        app(CancelPushNotificationService::class)->cancel($notification);

        $this->assertDatabaseCount('jobs', 0);
        $this->assertSame(PushNotificationStatus::Cancelled, $notification->fresh()->status);

        (new SendPushNotificationJob($notification->id))->handle(app(DeliverPushNotification::class));

        Http::assertNothingSent();
        $this->assertSame(PushNotificationStatus::Cancelled, $notification->fresh()->status);
    }

    private function queuedNotification(?CarbonInterface $scheduledAt = null, bool $keepJob = false): PushNotification
    {
        config(['queue.default' => 'database']);

        $client = Client::factory()->withFirebase()->create();
        $device = DeviceUser::factory()->create([
            'client_id' => $client->id,
            'firebase_token' => 'fcm-device-token-value-1234',
        ]);

        $notification = app(QueuePushNotificationService::class)->queue(
            client: $client,
            externalUserIds: [$device->external_user_id],
            title: 'Hola',
            body: 'Cuerpo',
            imageUrl: null,
            data: ['order' => '10'],
            scheduledAt: $scheduledAt,
            origin: PushOrigin::Api,
            requestId: null,
        );

        if (! $keepJob && $notification->job_id) {
            DB::table('jobs')->where('id', $notification->job_id)->delete();
            $notification->forceFill(['job_id' => null])->save();
        }

        return $notification->fresh(['recipients.deviceUser', 'client']);
    }
}
