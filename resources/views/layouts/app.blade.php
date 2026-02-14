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
        {{-- Alpine.js is bundled with Livewire 3, don't load it separately --}}
    @endif
    @livewireStyles
</head>
<body class="antialiased bg-gray-50" x-data>
    <div class="min-h-screen">
        @auth
            @php
                $allowedCompanies = auth()->user()?->ownedCompanies()->orderBy('name')->get() ?? collect();
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
                            @if (auth()->user()->canCreateArticles() || auth()->user()->canEditArticles())
                                <a href="{{ route('blog.articles.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">📝 Blog</a>
                            @endif
                            @if (auth()->user()->canViewInventory())
                                <div class="relative group">
                                    <button type="button" class="text-sm font-medium text-gray-600 hover:text-gray-900 inline-flex items-center gap-1">
                                        📦 Inventory
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                    <div class="absolute right-0 top-full pt-1 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-150 z-40">
                                        <div class="bg-white rounded-md shadow-lg border border-gray-200 py-1 min-w-[160px]">
                                            <a href="{{ route('inventory.products.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">🏷️ Products</a>
                                            <a href="{{ route('inventory.categories.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">📂 Categories</a>
                                            <a href="{{ route('inventory.units.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">📏 Units</a>
                                            <a href="{{ route('inventory.suppliers.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">🏭 Suppliers</a>
                                            <a href="{{ route('inventory.stock-locations.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">📍 Locations</a>
                                            <a href="{{ route('inventory.stocks.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">📊 Stock</a>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <div class="relative group">
                                <button type="button" class="text-sm font-medium text-gray-600 hover:text-gray-900 inline-flex items-center gap-1">
                                    💰 Sales
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div class="absolute right-0 top-full pt-1 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-150 z-40">
                                    <div class="bg-white rounded-md shadow-lg border border-gray-200 py-1 min-w-[160px]">
                                        <a href="{{ route('sales.quotes.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">📋 Quotes</a>
                                        <a href="{{ route('sales.orders.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">🛒 Orders</a>
                                        <a href="{{ route('sales.deliveries.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">🚚 Deliveries</a>
                                        <a href="{{ route('sales.invoices.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">🧾 Invoices</a>
                                    </div>
                                </div>
                            </div>
                            <div class="relative group">
                                <button type="button" class="text-sm font-medium text-gray-600 hover:text-gray-900 inline-flex items-center gap-1">
                                    👥 Customers
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div class="absolute right-0 top-full pt-1 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-150 z-40">
                                    <div class="bg-white rounded-md shadow-lg border border-gray-200 py-1 min-w-[140px]">
                                        <a href="{{ route('customers.companies.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">🏢 Companies</a>
                                        <a href="{{ route('customers.contacts.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">👤 Contacts</a>
                                    </div>
                                </div>
                            </div>
                            <a href="{{ route('chat.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">💬 Talk</a>
                            @if (auth()->user()->roles()->where('slug', 'admin')->exists())
                                <div class="relative group">
                                    <button type="button" class="text-sm font-medium text-gray-600 hover:text-gray-900 inline-flex items-center gap-1">
                                        ⚙️ Admin
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                    <div class="absolute right-0 top-full pt-1 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-150 z-40">
                                        <div class="bg-white rounded-md shadow-lg border border-gray-200 py-1 min-w-[140px]">
                                            <a href="{{ route('admin.team-members.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">👨‍💼 Team members</a>
                                            <a href="{{ route('admin.companies.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">🏢 Companies</a>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <span class="text-sm text-gray-500">👤 {{ auth()->user()->name }}</span>
                            <form action="{{ route('logout') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-sm text-gray-600 hover:text-gray-900">🚪 Log out</button>
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
    
    @if (!file_exists(public_path('build/manifest.json')))
        {{-- Echo + Pusher from CDN when Vite isn't built --}}
        <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.3.0/dist/web/pusher.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
        <script>
            window.Echo = new Echo({
                broadcaster: 'reverb',
                key: '{{ config('reverb.apps.apps.0.key') }}',
                wsHost: window.location.hostname,
                wsPort: 8085,
                wssPort: 8085,
                forceTLS: false,
                enabledTransports: ['ws', 'wss'],
                authEndpoint: '/broadcasting/auth',
                auth: {
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                }
            });
        </script>
    @endif

    @stack('scripts')
</body>
</html>
