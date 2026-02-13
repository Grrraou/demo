<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OwnedCompanySwitchController extends Controller
{
    public function switch(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'owned_company_id' => ['required', 'integer', 'exists:owned_companies,id'],
        ]);

        $user = $request->user();
        $allowed = $user->ownedCompanies()->where('owned_companies.id', $validated['owned_company_id'])->exists();

        if (! $allowed) {
            return back()->with('error', 'You do not have access to this company.');
        }

        session(['current_owned_company_id' => $validated['owned_company_id']]);

        return back()->with('success', 'Company switched.');
    }
}
