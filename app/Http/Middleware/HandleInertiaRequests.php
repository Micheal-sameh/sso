<?php

namespace App\Http\Middleware;

use App\DTOs\UserDTO;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? UserDTO::fromModel($request->user())->toArray() : null,
            ],
            'flash' => [
                'type' => $request->session()->get('flash.type'),
                'message' => $request->session()->get('flash.message'),
            ],
            'csrf_token' => csrf_token(),
            'errors' => $request->session()->get('errors')
                ? $request->session()->get('errors')->getBag('default')->getMessages()
                : [],
        ];
    }
}
