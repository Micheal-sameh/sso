<?php

namespace App\Http\Middleware;

use App\Enums\AdminPermission;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        if (! Auth::user()->can(AdminPermission::ManageSso->value)) {
            abort(403);
        }

        return $next($request);
    }
}
