<?php

namespace App\Services\Payments;

use App\Exceptions\Donations\GatewayNotConfiguredException;
use App\Models\Donation;
use App\Models\DonationPlan;
use App\Services\Payments\Contracts\DonationGatewayContract;
use App\Services\Payments\DTOs\GatewayCallbackResult;
use App\Services\Payments\DTOs\GatewayInitiationResult;
use Illuminate\Http\Request;

/**
 * CIB merchant gateway. Same situation as NbeDonationGateway: CIB hands
 * over their integration guide only after merchant onboarding, so these
 * methods are stubbed until then. See NbeDonationGateway's docblock for the
 * activation path (flip services.cib.sandbox to false once implemented).
 */
class CibDonationGateway implements DonationGatewayContract
{
    public function initiate(Donation $donation): GatewayInitiationResult
    {
        throw new GatewayNotConfiguredException(
            'CIB gateway integration is not yet implemented — pending the bank\'s merchant API documentation.'
        );
    }

    public function verifySignature(Request $request): bool
    {
        throw new GatewayNotConfiguredException('CIB gateway integration is not yet implemented.');
    }

    public function parseCallback(Request $request): GatewayCallbackResult
    {
        throw new GatewayNotConfiguredException('CIB gateway integration is not yet implemented.');
    }

    public function chargeToken(DonationPlan $plan): GatewayCallbackResult
    {
        throw new GatewayNotConfiguredException('CIB gateway integration is not yet implemented.');
    }
}
