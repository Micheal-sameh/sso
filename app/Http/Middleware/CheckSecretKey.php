<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSecretKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $secretKey = $request->header('X-Secret-Key') ?? $request->input('secret_key');

        if (! $secretKey) {
            return response()->json(['error' => 'Secret key is required'], 401);
        }

        $project = \App\Models\Project::where('secret_key', $secretKey)->first();

        if (! $project) {
            return response()->json(['error' => 'Invalid secret key'], 401);
        }

        $request->merge(['project' => $project]);

        return $next($request);
    }
}
