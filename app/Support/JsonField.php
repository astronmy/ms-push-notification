<?php

namespace App\Support;

use Illuminate\Validation\ValidationException;

class JsonField
{
    /**
     * @return array<string, mixed>|null
     */
    public static function decode(?string $json, string $attribute): ?array
    {
        if ($json === null || trim($json) === '') {
            return null;
        }

        $decoded = json_decode($json, true);

        if (! is_array($decoded)) {
            throw ValidationException::withMessages([
                $attribute => 'Tiene que ser un JSON válido.',
            ]);
        }

        return $decoded;
    }
}
