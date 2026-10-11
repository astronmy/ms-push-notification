<?php

namespace App\Models;

use App\Enums\RecipientStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'push_notification_id',
    'device_user_id',
    'firebase_token',
    'status',
    'error_code',
    'error_message',
    'sent_at',
])]
class PushNotificationRecipient extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => RecipientStatus::class,
            'sent_at' => 'datetime',
        ];
    }

    public function pushNotification(): BelongsTo
    {
        return $this->belongsTo(PushNotification::class);
    }

    public function deviceUser(): BelongsTo
    {
        return $this->belongsTo(DeviceUser::class);
    }

    public function tokenHint(): string
    {
        $token = (string) $this->firebase_token;

        if (strlen($token) <= 8) {
            return '***';
        }

        return '***'.substr($token, -4);
    }
}
