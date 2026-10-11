<?php

namespace App\Enums;

enum Platform: string
{
    case Ios = 'ios';
    case Android = 'android';

    public function label(): string
    {
        return match ($this) {
            self::Ios => 'iOS',
            self::Android => 'Android',
        };
    }
}
