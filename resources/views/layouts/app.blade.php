<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} - @yield('title', 'ERP')</title>
    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @endif
    @livewireStyles
</head>
<body class="antialiased bg-gray-50" x-data>
    <div class="min-h-screen">
        @auth
            @php
                $allowedCompanies = auth()->user()->ownedCompanies()->orderBy('name')->get();
                $currentCompanyId = (int) session('current_owned_company_id');
                $currentCompany = $allowedCompanies->firstWhere('id', $currentCompanyId);
                $headerColor = $currentCompany && $currentCompany->color ? $currentCompany->color : '#6366f1';
            @endphp
            <header class="fixed top-0 left-0 right-0 z-30 border-b border-gray-200 shadow-sm"
                    style="background: linear-gradient(to right, #ffffff, {{ $headerColor }});">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between h-14 gap-4">
                        <div class="flex items-center gap-6">
                            <a href="{{ route('home') }}" class="text-lg font-semibold text-gray-900">{{ config('app.name') }}</a>
                            @if ($allowedCompanies->isNotEmpty())
                                <form action="{{ route('current-company.switch') }}" method="POST" class="flex items-center gap-2">
                                    @csrf
                                    @if ($currentCompany && $currentCompany->logoUrl())
                                        <img src="{{ $currentCompany->logoUrl() }}" alt="{{ $currentCompany->name }}" class="h-8 w-auto max-w-[100px] object-contain" title="{{ $currentCompany->name }}">
                                    @else
                                        <label for="header-company" class="text-sm font-medium text-gray-700 whitespace-nowrap">Company</label>
                                    @endif
                                    <select name="owned_company_id" id="header-company"
                                            onchange="this.form.submit()"
                                            class="rounded-md border border-gray-300 py-1.5 pl-2 pr-8 text-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white/80">
                                        @foreach ($allowedCompanies as $company)
                                            <option value="{{ $company->id }}" {{ $company->id === $currentCompanyId ? 'selected' : '' }}>
                                                {{ $company->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                            @endif
                        </div>
                        <nav class="flex items-center gap-4">
                            <a href="{{ route('customers.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Customers</a>
                            <a href="{{ route('contacts.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Contacts</a>
                            @if (auth()->user()->roles()->where('slug', 'admin')->exists())
                                <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Users</a>
                                <a href="{{ route('admin.companies.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Companies</a>
                            @endif
                            <span class="text-sm text-gray-500">{{ auth()->user()->name }}</span>
                            <form action="{{ route('logout') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-sm text-gray-600 hover:text-gray-900">Log out</button>
                            </form>
                        </nav>
                    </div>
                </div>
            </header>
            <main class="pt-14">
                @yield('content')
            </main>
        @else
            @yield('content')
        @endauth
    </div>
    @livewireScripts
</body>
</html>
