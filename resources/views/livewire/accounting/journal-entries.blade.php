<div class="h-full flex flex-col">
    <!-- Header -->
    <div class="bg-white border-b px-6 py-4 flex items-center justify-between flex-shrink-0">
        <div class="flex items-center gap-4">
            <h1 class="text-xl font-semibold text-gray-900">Journal Entries</h1>
        </div>
        <div class="flex items-center gap-3">
            <!-- Search -->
            <div class="relative">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search entries..." 
                    class="w-48 pl-10 pr-4 py-2 rounded-lg border border-gray-300 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <!-- Filters -->
            <select wire:model.live="filterStatus" class="px-3 py-2 rounded-lg border border-gray-300 text-sm">
                <option value="">All Status</option>
                @foreach($statuses as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterJournal" class="px-3 py-2 rounded-lg border border-gray-300 text-sm">
                <option value="">All Journals</option>
                @foreach($this->journals as $journal)
                    <option value="{{ $journal->id }}">{{ $journal->name }}</option>
                @endforeach
            </select>
            @if($canEdit)
                <a href="{{ route('accounting.journal-entry.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    New Entry
                </a>
            @endif
        </div>
    </div>

    <!-- Entries Table -->
    <div class="flex-1 overflow-auto p-6">
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Entry #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Journal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($entries as $entry)
                        @php
                            $statusColors = [
                                'draft' => 'bg-yellow-100 text-yellow-800',
                                'posted' => 'bg-green-100 text-green-800',
                                'reversed' => 'bg-red-100 text-red-800',
                            ];
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-mono text-sm text-gray-900">{{ $entry->entry_number }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $entry->entry_date->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $entry->journal->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $entry->reference ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                                {{ $entry->description ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900">
                                {{ number_format($entry->total_debit, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $statusColors[$entry->status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ ucfirst($entry->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <div class="flex items-center justify-end gap-2">
                                    <button wire:click="viewEntry({{ $entry->id }})" class="text-gray-400 hover:text-blue-600" title="View">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </button>
                                    @if($canEdit && $entry->status === 'draft')
                                        <a href="{{ route('accounting.journal-entry.edit', $entry) }}" class="text-gray-400 hover:text-blue-600" title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                            </svg>
                                        </a>
                                        <button wire:click="postEntry({{ $entry->id }})" wire:confirm="Post this journal entry? This action cannot be undone." class="text-gray-400 hover:text-green-600" title="Post">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </button>
                                        <button wire:click="deleteEntry({{ $entry->id }})" wire:confirm="Delete this journal entry?" class="text-gray-400 hover:text-red-600" title="Delete">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    @elseif($canEdit && $entry->status === 'posted' && !$entry->reversed_by_id)
                                        <button wire:click="reverseEntry({{ $entry->id }})" wire:confirm="Reverse this journal entry?" class="text-gray-400 hover:text-orange-600" title="Reverse">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                No journal entries found.
                                @if($canEdit)
                                    <a href="{{ route('accounting.journal-entry.create') }}" class="text-blue-600 hover:underline">Create your first entry</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $entries->links() }}
        </div>
    </div>

    <!-- View Entry Modal -->
    @if($showViewModal && $this->viewingEntry)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" wire:click.self="closeViewModal">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl mx-4 max-h-[90vh] overflow-y-auto">
                <div class="px-6 py-4 border-b flex items-center justify-between sticky top-0 bg-white">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Journal Entry {{ $this->viewingEntry->entry_number }}</h2>
                        <p class="text-sm text-gray-500">{{ $this->viewingEntry->entry_date->format('F d, Y') }}</p>
                    </div>
                    <button wire:click="closeViewModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="p-6 space-y-6">
                    <!-- Header info -->
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">Journal:</span>
                            <span class="ml-2 text-gray-900">{{ $this->viewingEntry->journal->name ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Status:</span>
                            @php
                                $statusColors = [
                                    'draft' => 'bg-yellow-100 text-yellow-800',
                                    'posted' => 'bg-green-100 text-green-800',
                                    'reversed' => 'bg-red-100 text-red-800',
                                ];
                            @endphp
                            <span class="ml-2 inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $statusColors[$this->viewingEntry->status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($this->viewingEntry->status) }}
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-500">Reference:</span>
                            <span class="ml-2 text-gray-900">{{ $this->viewingEntry->reference ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Currency:</span>
                            <span class="ml-2 text-gray-900">{{ $this->viewingEntry->currency_code }}</span>
                        </div>
                    </div>

                    @if($this->viewingEntry->description)
                        <div>
                            <span class="text-sm text-gray-500">Description:</span>
                            <p class="mt-1 text-gray-900">{{ $this->viewingEntry->description }}</p>
                        </div>
                    @endif

                    <!-- Lines -->
                    <div>
                        <h3 class="text-sm font-medium text-gray-700 mb-3">Entry Lines</h3>
                        <div class="border rounded-lg overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Account</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Description</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Debit</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">Credit</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($this->viewingEntry->lines as $line)
                                        <tr>
                                            <td class="px-4 py-2 text-sm">
                                                <span class="font-mono text-gray-600">{{ $line->account->code }}</span>
                                                <span class="text-gray-900 ml-2">{{ $line->account->name }}</span>
                                            </td>
                                            <td class="px-4 py-2 text-sm text-gray-500">{{ $line->description ?? '-' }}</td>
                                            <td class="px-4 py-2 text-sm text-right font-medium {{ $line->debit > 0 ? 'text-gray-900' : 'text-gray-400' }}">
                                                {{ $line->debit > 0 ? number_format($line->debit, 2) : '-' }}
                                            </td>
                                            <td class="px-4 py-2 text-sm text-right font-medium {{ $line->credit > 0 ? 'text-gray-900' : 'text-gray-400' }}">
                                                {{ $line->credit > 0 ? number_format($line->credit, 2) : '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gray-50">
                                    <tr>
                                        <td colspan="2" class="px-4 py-2 text-sm font-medium text-gray-700">Total</td>
                                        <td class="px-4 py-2 text-sm text-right font-semibold text-gray-900">{{ number_format($this->viewingEntry->total_debit, 2) }}</td>
                                        <td class="px-4 py-2 text-sm text-right font-semibold text-gray-900">{{ number_format($this->viewingEntry->total_credit, 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- Audit info -->
                    <div class="text-xs text-gray-500 border-t pt-4">
                        <p>Created by {{ $this->viewingEntry->createdBy->name ?? 'Unknown' }} on {{ $this->viewingEntry->created_at->format('M d, Y H:i') }}</p>
                        @if($this->viewingEntry->posted_at)
                            <p>Posted by {{ $this->viewingEntry->postedBy->name ?? 'Unknown' }} on {{ $this->viewingEntry->posted_at->format('M d, Y H:i') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
