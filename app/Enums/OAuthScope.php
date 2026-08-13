<?php

namespace App\Enums;

enum OAuthScope: string
{
    case OpenId = 'openid';
    case Profile = 'profile';
    case Email = 'email';

    public function description(): string
    {
        return match ($this) {
            self::OpenId => 'Know that you have an Avarewase account',
            self::Profile => 'View your name and profile picture',
            self::Email => 'View your email address',
        };
    }
}
