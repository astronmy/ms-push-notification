<?php

namespace App\Services\Logging;

use App\Models\ApiLog;
use App\Models\Client;
use App\Models\FirebaseLog;
use App\Models\FirebaseRequest;
use App\Models\PushNotification;

class FirebaseTrafficLogger
{
    public function __construct(private readonly SensitiveDataMasker $masker) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function request(
        ?Client $client,
        ?PushNotification $notification,
        string $action,
        string $method,
        array $payload,
    ): FirebaseRequest {
        return FirebaseRequest::query()->create([
            'client_id' => $client?->id,
            'push_notification_id' => $notification?->id,
            'api_log_id' => $this->apiLogId($notification),
            'action' => $action,
            'method' => $method,
            'status' => 0,
            'payload' => $this->masker->mask($payload),
            'response' => null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $response
     */
    public function response(
        FirebaseRequest $request,
        int $status,
        array $response,
    ): FirebaseLog {
        $request->update([
            'status' => $status,
            'response' => $this->masker->mask($response),
        ]);

        return FirebaseLog::query()->create([
            'client_id' => $request->client_id,
            'push_notification_id' => $request->push_notification_id,
            'api_log_id' => $request->api_log_id,
            'firebase_request_id' => $request->id,
            'action' => $request->action,
            'method' => $request->method,
            'status' => $status,
            'payload' => $request->payload,
            'response' => $this->masker->mask($response),
        ]);
    }

    private function apiLogId(?PushNotification $notification): ?int
    {
        if (! $notification?->request_id) {
            return null;
        }

        return ApiLog::query()->where('request_id', $notification->request_id)->value('id');
    }
}
