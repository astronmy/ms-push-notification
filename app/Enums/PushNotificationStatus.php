<?php

namespace App\Enums;

enum PushNotificationStatus: string
{
    case Scheduled = 'scheduled';
    case Pending = 'pending';
    case Processing = 'processing';
    case Sent = 'sent';
    case Failed = 'failed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Scheduled => 'Programado',
            self::Pending => 'Pendiente',
            self::Processing => 'En proceso',
            self::Sent => 'Enviado',
            self::Failed => 'Fallido',
            self::Cancelled => 'Cancelado',
        };
    }

    public function isCancellable(): bool
    {
        return $this === self::Scheduled || $this === self::Pending;
    }

    public function isEditable(): bool
    {
        return $this->isCancellable();
    }
}
