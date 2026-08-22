<?php

namespace App\Exceptions\Donations;

class FundInUseException extends \RuntimeException
{
    protected int $statusCode = 409;

    public function __construct(string $message = 'This fund still has donations recorded against it and cannot be deleted.')
    {
        parent::__construct($message);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
