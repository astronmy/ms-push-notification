<?php

namespace App\Http\Resources;

use App\Models\PushNotification;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PushNotification */
class PushNotificationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status->value,
            'title' => $this->title,
            'body' => $this->body,
            'image_url' => $this->image_url,
            'data' => $this->data,
            'scheduled_at' => $this->scheduled_at?->toIso8601String(),
            'attempts' => $this->attempts,
            'last_error' => $this->last_error,
            'sent_at' => $this->sent_at?->toIso8601String(),
            'failed_at' => $this->failed_at?->toIso8601String(),
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'recipients' => PushRecipientResource::collection($this->whenLoaded('recipients')),
        ];
    }
}
