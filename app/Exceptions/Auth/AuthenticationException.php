<?php

namespace App\Exceptions\Auth;

class AuthenticationException extends \RuntimeException
{
    protected int $statusCode = 401;

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
