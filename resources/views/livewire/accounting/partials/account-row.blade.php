@php
    $typeColors = [
        'asset' => 'bg-blue-100 text-blue-800',
        'liability' => 'bg-red-100 text-red-800',
        'equity' => 'bg-purple-100 text-purple-800',
        'revenue' => 'bg-green-100 text-green-800',
        'expense' => 'bg-orange-100 text-orange-800',
    ];
@endphp

<tr class="{{ !$account['is_active'] ? 'bg-gray-50 opacity-60' : '' }}">
    <td class="px-6 py-3 whitespace-nowrap">
        <span style="padding-left: {{ $account['level'] * 1.5 }}rem" class="font-mono text-sm text-gray-900">
            @if($account['level'] > 0)
                <span class="text-gray-300 mr-1">└</span>
            @endif
            {{ $account['code'] }}
        </span>
    </td>
    <td class="px-6 py-3">
        <div class="text-sm text-gray-900">{{ $account['name'] }}</div>
        @if($account['description'])
            <div class="text-xs text-gray-500 truncate max-w-xs">{{ $account['description'] }}</div>
        @endif
    </td>
    <td class="px-6 py-3 whitespace-nowrap">
        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $typeColors[$account['type']] ?? 'bg-gray-100 text-gray-800' }}">
            {{ $account['type_name'] }}
        </span>
    </td>
    <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500">
        {{ $account['subtype'] ? ucwords(str_replace('_', ' ', $account['subtype'])) : '-' }}
    </td>
    <td class="px-6 py-3 whitespace-nowrap text-center">
        @if($account['is_system'])
            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">System</span>
        @elseif($account['is_active'])
            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Active</span>
        @else
            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">Inactive</span>
        @endif
    </td>
    @if($canEdit)
        <td class="px-6 py-3 whitespace-nowrap text-right text-sm">
            <div class="flex items-center justify-end gap-2">
                <button wire:click="openCreateModal({{ $account['id'] }})" class="text-gray-400 hover:text-blue-600" title="Add sub-account">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </button>
                @if(!$account['is_system'])
                    <button wire:click="openEditModal({{ $account['id'] }})" class="text-gray-400 hover:text-blue-600" title="Edit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                        </svg>
                    </button>
                    <button wire:click="toggleActive({{ $account['id'] }})" class="text-gray-400 hover:text-yellow-600" title="{{ $account['is_active'] ? 'Deactivate' : 'Activate' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            @if($account['is_active'])
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            @endif
                        </svg>
                    </button>
                    <button wire:click="deleteAccount({{ $account['id'] }})" wire:confirm="Are you sure you want to delete this account?" class="text-gray-400 hover:text-red-600" title="Delete">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                @endif
            </div>
        </td>
    @endif
</tr>

@foreach($account['children'] as $child)
    @include('livewire.accounting.partials.account-row', ['account' => $child, 'canEdit' => $canEdit])
@endforeach
