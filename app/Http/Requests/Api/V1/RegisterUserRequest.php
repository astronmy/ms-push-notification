<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\Platform;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'external_user_id' => ['required', 'string', 'max:191'],
            'firebase_token' => ['required', 'string', 'max:4096'],
            'platform' => ['required', Rule::enum(Platform::class)],
            'name' => ['nullable', 'string', 'max:255'],
            'meta' => ['nullable', 'array'],
        ];
    }
}
