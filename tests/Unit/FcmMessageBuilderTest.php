<?php

namespace Tests\Unit;

use App\Enums\Platform;
use App\Models\Client;
use App\Models\PushNotification;
use App\Services\Firebase\FcmMessageBuilder;
use Tests\TestCase;

class FcmMessageBuilderTest extends TestCase
{
    public function test_android_message_includes_the_client_icon_name(): void
    {
        $client = new Client(['android_notification_icon' => 'ic_notification']);
        $notification = new PushNotification([
            'title' => 'Hola',
            'body' => 'Cuerpo',
            'image_url' => 'https://ejemplo.com/a.png',
        ]);

        $message = (new FcmMessageBuilder)->build('token', $notification, Platform::Android, $client);

        $this->assertSame('ic_notification', $message['message']['android']['notification']['icon']);
        $this->assertSame('https://ejemplo.com/a.png', $message['message']['android']['notification']['image']);
        $this->assertArrayNotHasKey('apns', $message['message']);
    }

    public function test_android_message_omits_the_icon_when_the_client_has_none(): void
    {
        $notification = new PushNotification([
            'title' => 'Hola',
            'body' => 'Cuerpo',
        ]);

        $message = (new FcmMessageBuilder)->build('token', $notification, Platform::Android, new Client);

        $this->assertArrayNotHasKey('notification', $message['message']['android']);
    }
}
