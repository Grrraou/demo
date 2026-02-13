@extends('layouts.app')

@section('title', 'Home')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900">{{ config('app.name') }}</h1>
        <p class="mt-2 text-gray-600">Laravel 11 ERP backend. API-first, clean architecture.</p>

        @auth
            <div class="mt-8 p-4 bg-white rounded-lg shadow border border-gray-200 max-w-md">
                <p class="text-gray-700">Logged in as <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->email }}).</p>
                @if (auth()->user()->roles()->where('slug', 'admin')->exists())
                    <p class="mt-2">
                        <a href="{{ route('admin.employees.index') }}" class="text-indigo-600 hover:text-indigo-800 font-medium">Manage employees →</a>
                    </p>
                @endif
                <form action="{{ route('logout') }}" method="POST" class="mt-4">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-sm font-medium rounded hover:bg-gray-700">
                        Log out
                    </button>
                </form>
            </div>
        @else
            <div class="mt-8 p-6 bg-white rounded-lg shadow border border-gray-200 max-w-md">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Log in</h2>
                <form action="{{ route('login') }}" method="POST">
                    @csrf
                    @if ($errors->has('email'))
                        <p class="text-sm text-red-600 mb-2">{{ $errors->first('email') }}</p>
                    @endif
                    <div class="space-y-3">
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                                   class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                            <input type="password" name="password" id="password" required
                                   class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="remember" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-600">Remember me</span>
                            </label>
                        </div>
                        <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                            Log in
                        </button>
                    </div>
                </form>
            </div>
        @endauth
    </div>
</div>
@endsection
