<?php

namespace App\Exceptions\Auth;

class OAuthClientException extends \RuntimeException
{
    protected int $statusCode = 400;

    public static function forbidden(string $message): self
    {
        $e = new self($message);
        $e->statusCode = 403;

        return $e;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
