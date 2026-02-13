@extends('layouts.app')

@section('title', 'Customers')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Customers</h1>
            <a href="{{ route('home') }}" class="text-sm text-indigo-600 hover:text-indigo-800">← Home</a>
        </div>

        <p class="mb-4 text-sm text-gray-600">Customer companies linked to your current company.</p>

        <div class="bg-white rounded-lg shadow overflow-hidden border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contacts</th>
                        @if (auth()->user()->canEditCustomers())
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($customerCompanies as $company)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <a href="{{ route('customers.show', $company) }}" class="text-indigo-600 hover:text-indigo-800">{{ $company->name }}</a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $company->email ?? '—' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $company->phone ?? '—' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $company->contacts_count }}</td>
                            @if (auth()->user()->canEditCustomers())
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <a href="{{ route('customers.edit', $company) }}" class="text-indigo-600 hover:text-indigo-800">Edit</a>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->canEditCustomers() ? 5 : 4 }}" class="px-6 py-8 text-center text-gray-500">No customer companies for this environment.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if ($customerCompanies->hasPages())
                <div class="px-6 py-3 border-t border-gray-200">
                    {{ $customerCompanies->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
