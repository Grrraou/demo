<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTeamMemberCanViewInventory
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->canViewInventory()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Forbidden'], 403);
            }
            abort(403, 'Forbidden');
        }

        return $next($request);
    }
}
