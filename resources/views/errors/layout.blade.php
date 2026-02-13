<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Error') - {{ config('app.name') }}</title>
    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
</head>
<body class="antialiased bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-2xl">
        <div class="bg-white rounded-lg shadow border border-gray-200 p-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-2">@yield('heading', 'Error')</h1>
            <p class="text-gray-600 mb-6">@yield('message', 'Something went wrong.')</p>
            @yield('content')
            <a href="{{ url('/') }}" class="inline-block text-sm font-medium text-indigo-600 hover:text-indigo-800">← Back to home</a>

            @php
                $showDebug = false;
                try {
                    $showDebug = app()->environment('local', 'dev')
                        || (auth()->check() && auth()->user()?->roles()->where('slug', 'admin')->exists());
                } catch (Throwable $e) {
                    // Avoid breaking the error page (e.g. if DB is down)
                }
            @endphp
            @if ($showDebug && isset($exception))
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <h2 class="text-sm font-semibold text-gray-900 mb-2">Debug (admin or dev only)</h2>
                    <p class="text-xs font-mono text-red-700 mb-1"><strong>{{ get_class($exception) }}</strong></p>
                    <p class="text-xs text-gray-700 mb-2">{{ $exception->getMessage() }}</p>
                    <p class="text-xs text-gray-500 mb-2">{{ $exception->getFile() }}:{{ $exception->getLine() }}</p>
                    <pre class="text-xs text-gray-600 bg-gray-100 p-3 rounded overflow-auto max-h-48">{{ $exception->getTraceAsString() }}</pre>
                </div>
            @endif
        </div>
    </div>
</body>
</html>
