<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canPermission('clientes.editar') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'firebase_project_id' => ['nullable', 'string', 'max:255'],
            'android_notification_icon' => ['nullable', 'string', 'max:100', 'regex:/^[a-z][a-z0-9_]*$/'],
            'service_account' => ['nullable', 'file', 'max:200'],
            'rate_limit_per_minute' => ['nullable', 'integer', 'min:1', 'max:100000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'android_notification_icon.regex' => 'El ícono de Android solo puede tener minúsculas, números y guiones bajos. Ejemplo: ic_notification.',
        ];
    }
}
