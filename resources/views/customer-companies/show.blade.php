@extends('layouts.app')

@section('title', $customerCompany->name)

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6 flex items-center justify-between gap-4">
            <a href="{{ route('customers.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">← Customers</a>
            @if (auth()->user()->canEditCustomers())
                <a href="{{ route('customers.edit', $customerCompany) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Edit company</a>
            @endif
        </div>

        @if (session('success'))
            <p class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</p>
        @endif

        <div class="bg-white rounded-lg shadow border border-gray-200 p-6 mb-8">
            <h1 class="text-xl font-bold text-gray-900 mb-4">{{ $customerCompany->name }}</h1>
            <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                @if ($customerCompany->email)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                        <dd class="mt-0.5 text-sm text-gray-900">{{ $customerCompany->email }}</dd>
                    </div>
                @endif
                @if ($customerCompany->phone)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Phone</dt>
                        <dd class="mt-0.5 text-sm text-gray-900">{{ $customerCompany->phone }}</dd>
                    </div>
                @endif
                @if ($customerCompany->address)
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Address</dt>
                        <dd class="mt-0.5 text-sm text-gray-900 whitespace-pre-line">{{ $customerCompany->address }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Contacts ({{ $customerCompany->contacts->count() }})</h2>
            </div>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Job title</th>
                        @if (auth()->user()->canEditCustomers())
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($customerCompany->contacts as $contact)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $contact->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $contact->email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $contact->phone ?? '—' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $contact->job_title ?? '—' }}</td>
                            @if (auth()->user()->canEditCustomers())
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <a href="{{ route('contacts.edit', $contact) }}" class="text-indigo-600 hover:text-indigo-800">Edit</a>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->canEditCustomers() ? 5 : 4 }}" class="px-6 py-8 text-center text-gray-500">No contacts.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
