<?php

namespace App\Models;

use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'name',
    'is_active',
    'contact_name',
    'contact_email',
    'notes',
    'api_key_hash',
    'api_key_prefix',
    'api_key_last_four',
    'firebase_project_id',
    'firebase_service_account',
    'rate_limit_per_minute',
])]
#[Hidden(['api_key_hash', 'firebase_service_account'])]
class Client extends Model
{
    /** @use HasFactory<ClientFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'firebase_service_account' => 'encrypted:array',
            'rate_limit_per_minute' => 'integer',
        ];
    }

    public function deviceUsers(): HasMany
    {
        return $this->hasMany(DeviceUser::class);
    }

    public function pushNotifications(): HasMany
    {
        return $this->hasMany(PushNotification::class);
    }

    public function apiLogs(): HasMany
    {
        return $this->hasMany(ApiLog::class);
    }

    public function hasFirebase(): bool
    {
        return filled($this->firebase_project_id)
            && is_array($this->firebase_service_account)
            && filled($this->firebase_service_account['private_key'] ?? null)
            && filled($this->firebase_service_account['client_email'] ?? null);
    }

    public function maskedApiKey(): string
    {
        return $this->api_key_prefix.'…'.$this->api_key_last_four;
    }
}
