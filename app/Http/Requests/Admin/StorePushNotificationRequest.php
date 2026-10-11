<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePushNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canPermission('notificaciones.crear') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'client_id' => ['required', 'exists:clients,id'],
            'external_user_ids' => ['required', 'array', 'min:1'],
            'external_user_ids.*' => ['required', 'string', 'max:191'],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:4000'],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'data_json' => ['nullable', 'string'],
            'scheduled_at' => ['nullable', 'date'],
        ];
    }
}
