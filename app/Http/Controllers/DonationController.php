<?php

namespace App\Http\Controllers;

use App\DTOs\DonationDTO;
use App\Http\Requests\DonationRequest;
use App\Http\Traits\HandlesInertiaResponses;
use App\Models\Donation;
use App\Services\DonationService;
use App\Services\FundService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DonationController extends BaseController
{
    use HandlesInertiaResponses;

    public function __construct(
        protected DonationService $donationService,
        protected FundService $fundService,
    ) {}

    public function create(): Response
    {
        return $this->inertia('Donations/Create', [
            'funds' => $this->fundService->listActive(),
        ]);
    }

    public function store(DonationRequest $request): RedirectResponse
    {
        $dto = DonationDTO::fromRequest($request, Auth::user());

        ['redirect_url' => $redirectUrl] = $this->donationService->createDonation($dto);

        return Inertia::location($redirectUrl);
    }

    public function history(): Response
    {
        return $this->inertia('Donations/History', [
            'donations' => $this->donationService->listForUser(Auth::user())
                ->through(fn (Donation $donation) => $this->donationService->toArray($donation)),
        ]);
    }

    public function returnPage(Request $request): Response
    {
        $donation = Donation::where('uuid', $request->query('donation'))
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return $this->inertia('Donations/Return', [
            'donation' => $this->donationService->toArray($donation),
        ]);
    }

    /**
     * Sandbox "fake bank" checkout page — stands in for the real hosted
     * payment page until NBE/CIB merchant onboarding is complete. See
     * MockDonationGateway.
     */
    public function sandbox(Donation $donation): Response
    {
        abort_unless($donation->user_id === Auth::id() && $donation->status === 'pending', 404);

        return $this->inertia('Donations/Sandbox', [
            'donation' => $this->donationService->toArray($donation),
        ]);
    }

    public function sandboxPay(Request $request, Donation $donation): RedirectResponse
    {
        abort_unless($donation->user_id === Auth::id() && $donation->status === 'pending', 404);

        $this->donationService->markSandboxResult($donation, $request->boolean('success', true));

        return redirect()->route('donations.return', ['donation' => $donation->uuid]);
    }
}
