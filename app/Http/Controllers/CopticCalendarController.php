<?php

namespace App\Http\Controllers;

use App\Http\Traits\HandlesInertiaResponses;
use App\Services\CopticCalendarService;
use Illuminate\Http\Request;
use Inertia\Response;

class CopticCalendarController extends BaseController
{
    use HandlesInertiaResponses;

    public function __construct(
        protected CopticCalendarService $copticCalendarService,
    ) {}

    public function show(Request $request): Response
    {
        $date = $request->query('date');
        $date = is_string($date) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ? $date : null;

        return $this->inertia('Coptic/Show', [
            'coptic' => $this->copticCalendarService->forDate($date),
            'date' => $date ?? now()->toDateString(),
        ]);
    }
}
