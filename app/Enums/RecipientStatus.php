<?php

namespace App\Enums;

enum RecipientStatus: string
{
    case Pending = 'pending';
    case Sent = 'sent';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendiente',
            self::Sent => 'Enviado',
            self::Failed => 'Fallido',
        };
    }
}
