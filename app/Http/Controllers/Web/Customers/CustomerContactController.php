<?php

namespace App\Http\Controllers\Web\Customers;

use App\Http\Controllers\Controller;
use App\Models\CustomerContact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerContactController extends Controller
{
    public function index(Request $request): View
    {
        $ownedCompanyId = (int) $request->session()->get('current_owned_company_id');

        $contacts = CustomerContact::query()
            ->whereHas('customerCompany.ownedCompanies', fn ($q) => $q->where('owned_companies.id', $ownedCompanyId))
            ->with('customerCompany')
            ->orderBy('name')
            ->paginate($request->integer('per_page', 15));

        return view('customer-contacts.index', compact('contacts'));
    }

    public function edit(Request $request, CustomerContact $customerContact): View|RedirectResponse
    {
        $ownedCompanyId = (int) $request->session()->get('current_owned_company_id');
        if (! $customerContact->customerCompany->ownedCompanies()->where('owned_companies.id', $ownedCompanyId)->exists()) {
            abort(404);
        }

        $customerContact->load('customerCompany');

        return view('customer-contacts.edit', compact('customerContact'));
    }

    public function update(Request $request, CustomerContact $customerContact): RedirectResponse
    {
        $ownedCompanyId = (int) $request->session()->get('current_owned_company_id');
        if (! $customerContact->customerCompany->ownedCompanies()->where('owned_companies.id', $ownedCompanyId)->exists()) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'job_title' => ['nullable', 'string', 'max:100'],
        ]);

        $customerContact->update($validated);

        return redirect()->route('customers.contacts.index')->with('success', 'Contact updated.');
    }
}
