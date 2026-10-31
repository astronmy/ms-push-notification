<?php

namespace App\Models;

use App\Enums\RecipientStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
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

    /**
     * Destinatarios que todavía hay que intentar enviar: pendientes o con error transitorio.
     */
    public function scopeDeliverable(Builder $query): Builder
    {
        return $query->where(function (Builder $query) {
            $query->where('status', RecipientStatus::Pending->value)
                ->orWhere(fn (Builder $query) => $query
                    ->where('status', RecipientStatus::Failed->value)
                    ->where('error_code', 'transient'));
        });
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
