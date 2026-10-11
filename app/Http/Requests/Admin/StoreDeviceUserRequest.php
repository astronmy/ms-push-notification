<?php

namespace App\Http\Requests\Admin;

use App\Enums\Platform;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDeviceUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canPermission('dispositivos.crear') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'client_id' => ['required', 'exists:clients,id'],
            'external_user_id' => ['required', 'string', 'max:191'],
            'firebase_token' => ['required', 'string', 'max:4096'],
            'platform' => ['required', Rule::enum(Platform::class)],
            'name' => ['nullable', 'string', 'max:255'],
            'meta_json' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
