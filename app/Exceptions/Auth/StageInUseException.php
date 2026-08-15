<?php

namespace App\Exceptions\Auth;

class StageInUseException extends \RuntimeException
{
    protected int $statusCode = 409;

    public function __construct(string $message = 'This stage still has users assigned to it and cannot be deleted.')
    {
        parent::__construct($message);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
