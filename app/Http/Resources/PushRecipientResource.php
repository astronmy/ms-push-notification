<?php

namespace App\Http\Resources;

use App\Models\PushNotificationRecipient;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PushNotificationRecipient */
class PushRecipientResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'external_user_id' => $this->deviceUser?->external_user_id,
            'platform' => $this->deviceUser?->platform?->value,
            'status' => $this->status->value,
            'error_code' => $this->error_code,
            'error_message' => $this->error_message,
            'token_hint' => $this->tokenHint(),
            'sent_at' => $this->sent_at?->toIso8601String(),
        ];
    }
}
