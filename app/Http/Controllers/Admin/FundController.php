<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Http\Requests\FundRequest;
use App\Http\Traits\HandlesInertiaResponses;
use App\Models\Fund;
use App\Services\FundService;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

class FundController extends BaseController
{
    use HandlesInertiaResponses;

    public function __construct(
        protected FundService $fundService,
    ) {}

    public function index(): Response
    {
        return $this->inertia('Admin/Funds/Index', [
            'funds' => $this->fundService->listAll(),
        ]);
    }

    public function store(FundRequest $request): RedirectResponse
    {
        $this->fundService->create($request->validated());

        return $this->redirectRoute('admin.funds.index', 'Fund created successfully.');
    }

    public function update(Fund $fund, FundRequest $request): RedirectResponse
    {
        $this->fundService->update($fund, $request->validated());

        return $this->redirectRoute('admin.funds.index', 'Fund updated successfully.');
    }

    public function destroy(Fund $fund): RedirectResponse
    {
        $this->fundService->delete($fund);

        return $this->redirectBack('Fund deleted.', 'success');
    }
}
