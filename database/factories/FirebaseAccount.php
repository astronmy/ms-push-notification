<?php

namespace Database\Factories;

class FirebaseAccount
{
    /**
     * @var array<string, mixed>|null
     */
    private static ?array $account = null;

    /**
     * @return array<string, mixed>
     */
    public static function make(string $project = 'demo-project'): array
    {
        if (self::$account !== null && (self::$account['project_id'] ?? null) === $project) {
            return self::$account;
        }

        $resource = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        if ($resource === false) {
            throw new \RuntimeException('No se pudo generar una clave RSA de prueba.');
        }

        openssl_pkey_export($resource, $private);

        self::$account = [
            'type' => 'service_account',
            'project_id' => $project,
            'private_key_id' => 'test-key',
            'private_key' => $private,
            'client_email' => 'firebase-adminsdk@'.$project.'.iam.gserviceaccount.com',
            'client_id' => '1234567890',
            'token_uri' => 'https://oauth2.googleapis.com/token',
        ];

        return self::$account;
    }
}
