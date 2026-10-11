<?php

namespace App\Http\Requests\Admin;

use App\Enums\Platform;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDeviceUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canPermission('dispositivos.editar') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'firebase_token' => ['required', 'string', 'max:4096'],
            'platform' => ['required', Rule::enum(Platform::class)],
            'name' => ['nullable', 'string', 'max:255'],
            'meta_json' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
