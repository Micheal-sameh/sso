<?php

namespace App\Enums;

enum ClientGrantType: string
{
    case AuthorizationCode = 'authorization_code';
    case Implicit = 'implicit';

    public function label(): string
    {
        return match ($this) {
            self::AuthorizationCode => 'Authorization Code (PKCE)',
            self::Implicit => 'Implicit (legacy, e.g. Odoo)',
        };
    }
}
