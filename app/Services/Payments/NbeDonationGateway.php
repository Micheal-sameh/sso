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
 * National Bank of Egypt merchant gateway. NBE does not publish a public
 * self-serve API — endpoints, auth, and callback format come from their
 * integration guide only after merchant onboarding is approved
 * (config('services.nbe')). Fill in each method below once that guide is
 * in hand; until then services.nbe.sandbox stays true and
 * DonationGatewayFactory routes traffic to MockDonationGateway instead.
 */
class NbeDonationGateway implements DonationGatewayContract
{
    public function initiate(Donation $donation): GatewayInitiationResult
    {
        throw new GatewayNotConfiguredException(
            'NBE gateway integration is not yet implemented — pending the bank\'s merchant API documentation.'
        );
    }

    public function verifySignature(Request $request): bool
    {
        throw new GatewayNotConfiguredException('NBE gateway integration is not yet implemented.');
    }

    public function parseCallback(Request $request): GatewayCallbackResult
    {
        throw new GatewayNotConfiguredException('NBE gateway integration is not yet implemented.');
    }

    public function chargeToken(DonationPlan $plan): GatewayCallbackResult
    {
        throw new GatewayNotConfiguredException('NBE gateway integration is not yet implemented.');
    }
}
