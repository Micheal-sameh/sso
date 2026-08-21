<?php

namespace App\Enums;

enum DonationGateway: string
{
    case Nbe = 'nbe';
    case Cib = 'cib';

    public function label(): string
    {
        return match ($this) {
            self::Nbe => 'National Bank of Egypt',
            self::Cib => 'CIB',
        };
    }
}
