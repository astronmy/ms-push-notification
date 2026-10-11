<?php

namespace App\Models;

use App\Enums\Platform;
use Database\Factories\DeviceUserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'client_id',
    'external_user_id',
    'firebase_token',
    'platform',
    'name',
    'meta',
    'is_active',
    'invalidated_at',
    'last_registered_at',
])]
#[Hidden(['firebase_token'])]
class DeviceUser extends Model
{
    /** @use HasFactory<DeviceUserFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'platform' => Platform::class,
            'meta' => 'array',
            'is_active' => 'boolean',
            'invalidated_at' => 'datetime',
            'last_registered_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(PushNotificationRecipient::class);
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
