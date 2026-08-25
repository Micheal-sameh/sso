<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\UserDTO;
use App\Http\Controllers\BaseController;
use App\Http\Traits\HandlesInertiaResponses;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Response;

class BirthdayController extends BaseController
{
    use HandlesInertiaResponses;

    public function __construct(
        protected UserService $userService,
    ) {}

    public function index(Request $request): Response
    {
        $today = now()->toDateString();
        $date = $request->query('date');
        $date = is_string($date) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ? $date : $today;
        $parsed = Carbon::parse($date);

        return $this->inertia('Admin/Birthdays/Index', [
            'users' => $this->userService->birthdaysOnDate($parsed->month, $parsed->day)
                ->map(fn (User $user) => UserDTO::fromModel($user)->toArray())
                ->values(),
            'date' => $date,
            'today' => $today,
        ]);
    }
}
