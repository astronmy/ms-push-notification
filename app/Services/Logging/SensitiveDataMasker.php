<?php

namespace App\Services\Logging;

class SensitiveDataMasker
{
    /**
     * @var list<string>
     */
    private array $keys = [
        'password',
        'api_key',
        'authorization',
        'x-api-key',
        'firebase_token',
        'token',
        'service_account',
        'private_key',
        'client_email',
        'private_key_id',
        'client_secret',
        'access_token',
        'assertion',
        'firebase_service_account',
    ];

    public function mask(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        $masked = [];

        foreach ($value as $key => $item) {
            if (is_string($key) && $this->isSensitive($key)) {
                $masked[$key] = $this->maskScalar($item);

                continue;
            }

            $masked[$key] = is_array($item) ? $this->mask($item) : $item;
        }

        return $masked;
    }

    public function hint(?string $value): string
    {
        if ($value === null || strlen($value) <= 8) {
            return '***';
        }

        return '***'.substr($value, -4);
    }

    private function isSensitive(string $key): bool
    {
        return in_array(strtolower($key), $this->keys, true);
    }

    private function maskScalar(mixed $value): string
    {
        if (! is_string($value)) {
            return '***';
        }

        return $this->hint($value);
    }
}
