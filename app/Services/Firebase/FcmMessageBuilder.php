<?php

namespace App\Services\Firebase;

use App\Enums\Platform;
use App\Models\PushNotification;

class FcmMessageBuilder
{
    /**
     * @return array<string, mixed>
     */
    public function build(string $token, PushNotification $notification, Platform $platform): array
    {
        $message = [
            'token' => $token,
            'notification' => array_filter([
                'title' => $notification->title,
                'body' => $notification->body,
                'image' => $notification->image_url,
            ], fn (mixed $value) => $value !== null && $value !== ''),
            'android' => [
                'priority' => 'HIGH',
            ],
            'apns' => [
                'headers' => [
                    'apns-priority' => '10',
                ],
                'payload' => [
                    'aps' => [
                        'sound' => 'default',
                    ],
                ],
            ],
        ];

        $data = $this->stringifyData($notification->data ?? []);

        if ($data !== []) {
            $message['data'] = $data;
        }

        if (filled($notification->image_url)) {
            $message['android']['notification'] = ['image' => $notification->image_url];
            $message['apns']['payload']['aps']['mutable-content'] = 1;
            $message['apns']['fcm_options'] = ['image' => $notification->image_url];
        }

        if ($platform === Platform::Android) {
            unset($message['apns']);
        }

        if ($platform === Platform::Ios) {
            unset($message['android']);
        }

        return ['message' => $message];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, string>
     */
    private function stringifyData(array $data): array
    {
        $stringified = [];

        foreach ($data as $key => $value) {
            if (! is_string($key) || $key === '') {
                continue;
            }

            if (is_array($value)) {
                $stringified[$key] = (string) json_encode($value);

                continue;
            }

            if (is_bool($value)) {
                $stringified[$key] = $value ? 'true' : 'false';

                continue;
            }

            if ($value === null) {
                $stringified[$key] = '';

                continue;
            }

            $stringified[$key] = (string) $value;
        }

        return $stringified;
    }
}
