<?php

namespace App\Services\Firebase;

use App\Exceptions\TransientFcmException;
use App\Models\Client;
use App\Models\PushNotification;
use App\Services\Logging\FirebaseTrafficLogger;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GoogleAccessToken
{
    public function __construct(private readonly FirebaseTrafficLogger $logger) {}

    public function forClient(Client $client, ?PushNotification $notification = null): string
    {
        $account = $client->firebase_service_account;

        if (! is_array($account)) {
            throw new TransientFcmException('El cliente no tiene service account de Firebase.');
        }

        $cacheKey = $this->cacheKey($client, $account);

        $cached = Cache::get($cacheKey);

        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $assertion = $this->assertion($account);
        $payload = [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $assertion,
        ];

        $requestLog = $this->logger->request(
            $client,
            $notification,
            'oauthToken',
            'POST',
            ['url' => config('push.fcm.oauth_url'), 'body' => $payload],
        );

        try {
            $response = Http::asForm()
                ->acceptJson()
                ->timeout(15)
                ->post((string) config('push.fcm.oauth_url'), $payload);
        } catch (ConnectionException $exception) {
            $this->logger->response($requestLog, 0, ['message' => $exception->getMessage()]);

            throw new TransientFcmException('No se pudo conectar con Google OAuth.', 0, $exception);
        }

        $body = $response->json();
        $decoded = is_array($body) ? $body : ['raw' => $response->body()];
        $this->logger->response($requestLog, $response->status(), $decoded);

        $token = $decoded['access_token'] ?? null;

        if (! $response->successful() || ! is_string($token) || $token === '') {
            throw new TransientFcmException('Google OAuth rechazó la service account.');
        }

        Cache::put($cacheKey, $token, now()->addMinutes((int) config('push.fcm.token_ttl_minutes', 50)));

        return $token;
    }

    /**
     * @param  array<string, mixed>  $account
     */
    private function cacheKey(Client $client, array $account): string
    {
        $fingerprint = hash('sha256', (string) ($account['private_key_id'] ?? $account['client_email'] ?? ''));

        return 'fcm-access:'.$client->id.':'.substr($fingerprint, 0, 16);
    }

    /**
     * @param  array<string, mixed>  $account
     */
    private function assertion(array $account): string
    {
        $now = time();
        $header = $this->base64Url((string) json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $claim = $this->base64Url((string) json_encode([
            'iss' => $account['client_email'] ?? '',
            'scope' => config('push.fcm.scope'),
            'aud' => config('push.fcm.oauth_url'),
            'iat' => $now,
            'exp' => $now + 3600,
        ]));
        $unsigned = $header.'.'.$claim;

        $privateKey = (string) ($account['private_key'] ?? '');
        $signature = '';
        $signed = openssl_sign($unsigned, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        if (! $signed) {
            throw new TransientFcmException('No se pudo firmar el JWT de la service account.');
        }

        return $unsigned.'.'.$this->base64Url($signature);
    }

    private function base64Url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
