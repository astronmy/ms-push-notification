<?php

namespace App\Http\Requests\Admin\Concerns;

/**
 * El formulario envía los destinatarios como un único campo JSON para no
 * superar el límite max_input_vars de PHP cuando se seleccionan muchos.
 */
trait DecodesExternalUserIds
{
    protected function prepareForValidation(): void
    {
        $ids = $this->input('external_user_ids');

        if (is_string($ids)) {
            $decoded = json_decode($ids, true);

            $this->merge([
                'external_user_ids' => is_array($decoded)
                    ? array_values(array_unique(array_map('strval', $decoded)))
                    : [],
            ]);
        }
    }
}
