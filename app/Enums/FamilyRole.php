<?php

namespace App\Enums;

enum FamilyRole: string
{
    case Father = 'father';
    case Mother = 'mother';
    case Child = 'child';

    public function label(): string
    {
        return match ($this) {
            self::Father => 'Father',
            self::Mother => 'Mother',
            self::Child => 'Child',
        };
    }
}
