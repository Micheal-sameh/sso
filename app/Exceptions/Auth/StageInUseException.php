<?php

namespace App\Exceptions\Auth;

class StageInUseException extends \RuntimeException
{
    protected int $statusCode = 409;

    public function __construct(?string $message = null)
    {
        parent::__construct($message ?? __('messages.stage_in_use'));
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
