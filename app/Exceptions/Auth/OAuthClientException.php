<?php

namespace App\Exceptions\Auth;

class OAuthClientException extends \RuntimeException
{
    protected int $statusCode = 400;

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
