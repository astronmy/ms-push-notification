<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'client_id',
    'push_notification_id',
    'api_log_id',
    'firebase_request_id',
    'action',
    'method',
    'status',
    'payload',
    'response',
])]
class FirebaseLog extends Model
{
    use SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'response' => 'array',
            'status' => 'integer',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function pushNotification(): BelongsTo
    {
        return $this->belongsTo(PushNotification::class);
    }

    public function apiLog(): BelongsTo
    {
        return $this->belongsTo(ApiLog::class);
    }

    public function firebaseRequest(): BelongsTo
    {
        return $this->belongsTo(FirebaseRequest::class);
    }
}
