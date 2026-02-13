<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCurrentOwnedCompany
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return $next($request);
        }

        $currentId = session('current_owned_company_id');
        $allowed = $request->user()->ownedCompanies()->pluck('owned_companies.id')->toArray();

        if (empty($allowed)) {
            session()->forget('current_owned_company_id');
            return $next($request);
        }

        if (! $currentId || ! in_array((int) $currentId, $allowed, true)) {
            session(['current_owned_company_id' => $allowed[0]]);
        }

        return $next($request);
    }
}
