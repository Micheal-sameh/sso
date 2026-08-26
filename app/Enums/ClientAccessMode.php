<?php

namespace App\Enums;

enum ClientAccessMode: string
{
    case All = 'all';
    case Stage = 'stage';
    case Specific = 'specific';
    case Group = 'group';
    case AllExceptUsers = 'all_except_users';

    public function label(): string
    {
        return match ($this) {
            self::All => 'All Users',
            self::Stage => 'Specific Stage(s)',
            self::Specific => 'Specific Users',
            self::Group => 'Specific Group(s)',
            self::AllExceptUsers => 'All Users Except Specific Users',
        };
    }
}
