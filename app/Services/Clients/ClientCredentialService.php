<?php

namespace App\Services\Clients;

use App\Models\Client;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ClientCredentialService
{
    public function issueKey(Client $client): string
    {
        $plain = 'pns_'.Str::random(48);

        $client->forceFill([
            'api_key_hash' => hash('sha256', $plain),
            'api_key_prefix' => substr($plain, 0, 12),
            'api_key_last_four' => substr($plain, -4),
        ])->save();

        return $plain;
    }

    public function storeServiceAccount(Client $client, ?string $json, ?string $projectId): void
    {
        if ($json === null || trim($json) === '') {
            if ($projectId) {
                $client->firebase_project_id = $projectId;
                $client->save();
            }

            return;
        }

        $decoded = json_decode($json, true);

        if (! is_array($decoded)) {
            throw ValidationException::withMessages([
                'service_account' => 'El archivo no es un JSON válido.',
            ]);
        }

        foreach (['project_id', 'client_email', 'private_key'] as $required) {
            if (! is_string($decoded[$required] ?? null) || $decoded[$required] === '') {
                throw ValidationException::withMessages([
                    'service_account' => 'La service account tiene que incluir project_id, client_email y private_key.',
                ]);
            }
        }

        if ($projectId && $projectId !== $decoded['project_id']) {
            throw ValidationException::withMessages([
                'firebase_project_id' => 'El project_id no coincide con el de la service account.',
            ]);
        }

        $client->firebase_project_id = $decoded['project_id'];
        $client->firebase_service_account = $decoded;
        $client->save();
    }
}
