<?php

namespace App\Services\Firebase;

use App\Exceptions\TransientFcmException;
use App\Models\Client;
use App\Models\PushNotification;
use App\Models\PushNotificationRecipient;
use App\Services\Logging\FirebaseTrafficLogger;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class FcmClient
{
    public function __construct(
        private readonly GoogleAccessToken $tokens,
        private readonly FcmMessageBuilder $messages,
        private readonly FirebaseTrafficLogger $logger,
    ) {}

    public function send(Client $client, PushNotification $notification, PushNotificationRecipient $recipient): FcmResult
    {
        $accessToken = $this->tokens->forClient($client, $notification);
        $body = $this->messages->build(
            $recipient->firebase_token,
            $notification,
            $recipient->deviceUser->platform,
            $client,
        );
        $url = sprintf((string) config('push.fcm.send_url'), $client->firebase_project_id);

        $requestLog = $this->logger->request($client, $notification, 'send', 'POST', [
            'url' => $url,
            'body' => $body,
        ]);

        try {
            $response = Http::withToken($accessToken)
                ->acceptJson()
                ->timeout(15)
                ->post($url, $body);
        } catch (ConnectionException $exception) {
            $this->logger->response($requestLog, 0, ['message' => $exception->getMessage()]);

            throw new TransientFcmException('No se pudo conectar con FCM.', 0, $exception);
        }

        $decoded = $response->json();
        $payload = is_array($decoded) ? $decoded : ['raw' => $response->body()];
        $this->logger->response($requestLog, $response->status(), $payload);

        return FcmResult::fromHttp($response->status(), $payload);
    }
}
