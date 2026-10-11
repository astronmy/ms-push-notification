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
            'service_account' => ['nullable', 'file', 'max:200'],
            'rate_limit_per_minute' => ['nullable', 'integer', 'min:1', 'max:100000'],
        ];
    }
}
