<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\Platform;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTokenRequest extends FormRequest
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
            'firebase_token' => ['required', 'string', 'max:4096'],
            'platform' => ['nullable', Rule::enum(Platform::class)],
        ];
    }
}
