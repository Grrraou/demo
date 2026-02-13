@extends('layouts.app')

@section('title', 'User: ' . $user->name)

@section('content')
<div class="py-12">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="{{ route('admin.users.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">← Manage users</a>
        </div>

        @if (session('success'))
            <p class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</p>
        @endif
        @if (session('error'))
            <p class="mb-4 p-3 bg-red-100 text-red-800 rounded">{{ session('error') }}</p>
        @endif

        <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
            <h1 class="text-xl font-bold text-gray-900 mb-6">User details</h1>

            <form action="{{ route('admin.users.update', $user) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                @if ($errors->any())
                    <ul class="text-sm text-red-600 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                @endif
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                           class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                           class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">New password (leave blank to keep)</label>
                    <input type="password" name="password" id="password"
                           class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm new password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation"
                           class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <span class="block text-sm font-medium text-gray-700 mb-2">Roles (ACL)</span>
                    <div class="space-y-2">
                        @forelse ($roles as $role)
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="role_ids[]" value="{{ $role->id }}"
                                       {{ $user->roles->contains($role) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-700">{{ $role->name }}</span>
                                <span class="ml-1 text-xs text-gray-500">({{ $role->slug }})</span>
                            </label>
                        @empty
                            <p class="text-sm text-gray-500">No roles defined.</p>
                        @endforelse
                    </div>
                </div>
                <div>
                    <span class="block text-sm font-medium text-gray-700 mb-2">Companies (access)</span>
                    <div class="space-y-2">
                        @forelse ($ownedCompanies as $company)
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="owned_company_ids[]" value="{{ $company->id }}"
                                       {{ $user->ownedCompanies->contains($company) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-700">{{ $company->name }}</span>
                                <span class="ml-1 text-xs text-gray-500">({{ $company->slug }})</span>
                            </label>
                        @empty
                            <p class="text-sm text-gray-500">No companies defined.</p>
                        @endforelse
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                        Save changes
                    </button>
                </div>
            </form>

            <hr class="my-8 border-gray-200">

            <div>
                <h2 class="text-sm font-semibold text-gray-900 mb-2">Delete user</h2>
                @if ($user->id === auth()->id())
                    <p class="text-sm text-gray-500">You cannot delete your own account.</p>
                @else
                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Delete this user?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700">
                            Delete user
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
