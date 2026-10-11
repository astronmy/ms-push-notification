<?php

namespace App\Models;

use App\Enums\PushNotificationStatus;
use App\Enums\PushOrigin;
use Database\Factories\PushNotificationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'client_id',
    'created_by',
    'origin',
    'title',
    'body',
    'image_url',
    'data',
    'scheduled_at',
    'status',
    'job_id',
    'request_id',
    'attempts',
    'last_error',
    'processing_started_at',
    'sent_at',
    'failed_at',
    'cancelled_at',
])]
class PushNotification extends Model
{
    /** @use HasFactory<PushNotificationFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'origin' => PushOrigin::class,
            'data' => 'array',
            'scheduled_at' => 'datetime',
            'status' => PushNotificationStatus::class,
            'attempts' => 'integer',
            'processing_started_at' => 'datetime',
            'sent_at' => 'datetime',
            'failed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(PushNotificationRecipient::class);
    }

    public function firebaseRequests(): HasMany
    {
        return $this->hasMany(FirebaseRequest::class);
    }

    public function firebaseLogs(): HasMany
    {
        return $this->hasMany(FirebaseLog::class);
    }
}
