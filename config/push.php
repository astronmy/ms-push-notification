<?php

return [

    'admin_name' => env('ADMIN_NAME', 'Administrador'),
    'admin_email' => env('ADMIN_EMAIL', 'admin@push.test'),
    'admin_password' => env('ADMIN_PASSWORD', 'password'),

    'demo_client_name' => env('DEMO_CLIENT_NAME', 'Cliente demo'),
    'demo_client_api_key' => env('DEMO_CLIENT_API_KEY', 'pns_demo_local_key_change_me'),

    'fcm' => [
        'oauth_url' => 'https://oauth2.googleapis.com/token',
        'send_url' => 'https://fcm.googleapis.com/v1/projects/%s/messages:send',
        'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
        'token_ttl_minutes' => 50,
    ],

];
