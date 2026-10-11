<?php

namespace App\Enums;

enum PushOrigin: string
{
    case Api = 'api';
    case Backoffice = 'backoffice';

    public function label(): string
    {
        return match ($this) {
            self::Api => 'API',
            self::Backoffice => 'Backoffice',
        };
    }
}
