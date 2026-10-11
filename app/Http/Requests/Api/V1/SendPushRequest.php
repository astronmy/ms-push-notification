<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class SendPushRequest extends FormRequest
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
            'external_user_ids' => ['required', 'array', 'min:1'],
            'external_user_ids.*' => ['required', 'string', 'max:191'],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:4000'],
            'data' => ['nullable', 'array'],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'scheduled_at' => ['nullable', 'date'],
        ];
    }
}
