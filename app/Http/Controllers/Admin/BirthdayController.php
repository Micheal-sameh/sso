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
        $date = $this->validDate($request->query('date')) ?? $today;
        $to = $this->validDate($request->query('to'));

        // A range needs an end on or after the start; otherwise fall back to a single day.
        if ($to !== null && $to <= $date) {
            $to = null;
        }

        $from = Carbon::parse($date);
        $users = $to === null
            ? $this->userService->birthdaysOnDate($from->month, $from->day)
            : $this->userService->birthdaysBetween($from, Carbon::parse($to));

        return $this->inertia('Admin/Birthdays/Index', [
            'users' => $users
                ->map(fn (User $user) => UserDTO::fromModel($user)->toArray())
                ->values(),
            'date' => $date,
            'to' => $to,
            'today' => $today,
        ]);
    }

    /** Return the value if it is a real YYYY-MM-DD date, otherwise null. */
    private function validDate(mixed $value): ?string
    {
        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        try {
            return Carbon::createFromFormat('!Y-m-d', $value)->toDateString() === $value ? $value : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
