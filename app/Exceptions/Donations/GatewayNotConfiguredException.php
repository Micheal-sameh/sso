<?php

namespace App\Exceptions\Donations;

class GatewayNotConfiguredException extends \RuntimeException
{
    protected int $statusCode = 503;

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
