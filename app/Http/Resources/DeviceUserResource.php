<?php

namespace App\Http\Resources;

use App\Models\DeviceUser;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin DeviceUser */
class DeviceUserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'external_user_id' => $this->external_user_id,
            'platform' => $this->platform->value,
            'name' => $this->name,
            'meta' => $this->meta,
            'is_active' => $this->is_active,
            'token_hint' => $this->tokenHint(),
            'invalidated_at' => $this->invalidated_at?->toIso8601String(),
            'last_registered_at' => $this->last_registered_at?->toIso8601String(),
        ];
    }
}
