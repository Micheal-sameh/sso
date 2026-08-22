<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Http\Traits\HandlesInertiaResponses;
use App\Models\Donation;
use App\Services\DonationService;
use App\Services\FundService;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Response;

class DonationController extends BaseController
{
    use HandlesInertiaResponses;

    public function __construct(
        protected DonationService $donationService,
        protected FundService $fundService,
    ) {}

    public function index(Request $request): Response
    {
        $filters = $request->only(['status', 'fund_id', 'gateway', 'from', 'to']);

        return $this->inertia('Admin/Donations/Index', [
            'donations' => $this->donationService->listForAdmin($filters, 20)
                ->through(fn (Donation $donation) => $this->donationService->toArray($donation)),
            'totals' => $this->donationService->dashboardTotals(),
            'funds' => $this->fundService->listAll(),
            'filters' => $filters,
        ]);
    }

    public function export(Request $request): HttpResponse
    {
        $filters = $request->only(['status', 'fund_id', 'gateway', 'from', 'to']);
        $rows = $this->donationService->exportCsvRows($filters);

        $csv = implode("\n", array_map(
            fn (array $row) => implode(',', array_map(fn ($value) => '"'.str_replace('"', '""', (string) $value).'"', $row)),
            [['Date', 'Fund', 'Donor', 'Amount', 'Currency', 'Gateway', 'Status', 'Type'], ...$rows],
        ));

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="donations.csv"',
        ]);
    }
}
