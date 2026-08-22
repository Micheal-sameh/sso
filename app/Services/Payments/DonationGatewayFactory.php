<?php

namespace App\Services\Payments;

use App\Enums\DonationGateway as DonationGatewayEnum;
use App\Services\Payments\Contracts\DonationGatewayContract;
use Illuminate\Contracts\Container\Container;

class DonationGatewayFactory
{
    public function __construct(
        protected Container $container,
    ) {}

    public function make(DonationGatewayEnum $gateway): DonationGatewayContract
    {
        // Both bank integrations are sandboxed by default (no merchant API
        // docs yet) so the donation flow works end-to-end during
        // development. Once a bank approves onboarding and hands over its
        // integration guide, implement its gateway class and set
        // services.<gateway>.sandbox=false in .env to switch it live.
        if (config("services.{$gateway->value}.sandbox", true)) {
            return $this->container->make(MockDonationGateway::class);
        }

        return match ($gateway) {
            DonationGatewayEnum::Nbe => $this->container->make(NbeDonationGateway::class),
            DonationGatewayEnum::Cib => $this->container->make(CibDonationGateway::class),
        };
    }
}
