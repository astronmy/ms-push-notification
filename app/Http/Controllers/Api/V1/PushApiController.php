<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\PushOrigin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\RegisterUserRequest;
use App\Http\Requests\Api\V1\SendPushRequest;
use App\Http\Requests\Api\V1\UpdateTokenRequest;
use App\Http\Resources\DeviceUserResource;
use App\Http\Resources\PushNotificationResource;
use App\Models\Client;
use App\Models\PushNotification;
use App\Services\Devices\RegisterDeviceUserService;
use App\Services\Devices\UpdateDeviceTokenService;
use App\Services\Notifications\QueuePushNotificationService;
use App\Support\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushApiController extends Controller
{
    public function register(RegisterUserRequest $request, RegisterDeviceUserService $service): JsonResponse
    {
        $device = $service->upsert($this->client($request), $request->validated());

        return ApiResponse::success(
            new DeviceUserResource($device),
            $device->wasRecentlyCreated ? 'Usuario registrado.' : 'Usuario actualizado.',
            $device->wasRecentlyCreated ? 201 : 200,
        );
    }

    public function updateToken(UpdateTokenRequest $request, string $externalUserId, UpdateDeviceTokenService $service): JsonResponse
    {
        $device = $service->update($this->client($request), $externalUserId, $request->validated());

        return ApiResponse::success(new DeviceUserResource($device), 'Token actualizado.');
    }

    public function send(SendPushRequest $request, QueuePushNotificationService $service): JsonResponse
    {
        $data = $request->validated();
        $scheduledAt = isset($data['scheduled_at']) ? Carbon::parse($data['scheduled_at']) : null;

        $notification = $service->queue(
            client: $this->client($request),
            externalUserIds: $data['external_user_ids'],
            title: $data['title'],
            body: $data['body'],
            imageUrl: $data['image_url'] ?? null,
            data: $data['data'] ?? null,
            scheduledAt: $scheduledAt,
            origin: PushOrigin::Api,
            requestId: $request->attributes->get('request_id'),
        );

        return ApiResponse::success(
            new PushNotificationResource($notification),
            'Notificación encolada.',
            202,
        );
    }

    public function show(Request $request, int $pushNotification): JsonResponse
    {
        $notification = PushNotification::query()
            ->with('recipients.deviceUser')
            ->where('client_id', $this->client($request)->id)
            ->findOrFail($pushNotification);

        return ApiResponse::success(new PushNotificationResource($notification));
    }

    private function client(Request $request): Client
    {
        $client = $request->attributes->get('client');

        if (! $client instanceof Client) {
            abort(401, 'API key requerida.');
        }

        return $client;
    }
}
