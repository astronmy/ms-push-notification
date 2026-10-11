<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class DemoClientSeeder extends Seeder
{
    public function run(): void
    {
        $plain = (string) config('push.demo_client_api_key');

        Client::query()->updateOrCreate(
            ['name' => config('push.demo_client_name')],
            [
                'is_active' => true,
                'contact_name' => 'Demo',
                'contact_email' => 'demo@push.test',
                'notes' => 'Cliente local para probar la API. Cargá la service account de Firebase antes de enviar pushes.',
                'api_key_hash' => hash('sha256', $plain),
                'api_key_prefix' => substr($plain, 0, 12),
                'api_key_last_four' => substr($plain, -4),
            ],
        );
    }
}
