<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CustomerCompany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerCompanyController extends Controller
{
    public function index(Request $request): View
    {
        $ownedCompanyId = (int) $request->session()->get('current_owned_company_id');

        $customerCompanies = CustomerCompany::query()
            ->whereHas('ownedCompanies', fn ($q) => $q->where('owned_companies.id', $ownedCompanyId))
            ->withCount('contacts')
            ->orderBy('name')
            ->paginate($request->integer('per_page', 15));

        return view('customer-companies.index', compact('customerCompanies'));
    }

    public function show(Request $request, CustomerCompany $customerCompany): View
    {
        $ownedCompanyId = (int) $request->session()->get('current_owned_company_id');
        if (! $customerCompany->ownedCompanies()->where('owned_companies.id', $ownedCompanyId)->exists()) {
            abort(404);
        }

        $customerCompany->load('contacts');

        return view('customer-companies.show', compact('customerCompany'));
    }

    public function edit(Request $request, CustomerCompany $customerCompany): View|RedirectResponse
    {
        $ownedCompanyId = (int) $request->session()->get('current_owned_company_id');
        if (! $customerCompany->ownedCompanies()->where('owned_companies.id', $ownedCompanyId)->exists()) {
            abort(404);
        }

        return view('customer-companies.edit', compact('customerCompany'));
    }

    public function update(Request $request, CustomerCompany $customerCompany): RedirectResponse
    {
        $ownedCompanyId = (int) $request->session()->get('current_owned_company_id');
        if (! $customerCompany->ownedCompanies()->where('owned_companies.id', $ownedCompanyId)->exists()) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        $customerCompany->update($validated);

        return redirect()->route('customers.show', $customerCompany)->with('success', 'Customer company updated.');
    }
}
