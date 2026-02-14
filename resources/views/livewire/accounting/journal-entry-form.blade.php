<div class="h-full flex flex-col">
    <!-- Header -->
    <div class="bg-white border-b px-6 py-4 flex items-center justify-between flex-shrink-0">
        <div class="flex items-center gap-4">
            <a href="{{ route('accounting.journal-entries') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <h1 class="text-xl font-semibold text-gray-900">
                {{ $isEditing ? 'Edit Journal Entry' : 'New Journal Entry' }}
            </h1>
        </div>
        <div class="flex items-center gap-3">
            <div class="text-sm {{ $this->isBalanced ? 'text-green-600' : 'text-red-600' }}">
                @if($this->isBalanced)
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Balanced
                    </span>
                @else
                    <span>Difference: {{ number_format($this->difference, 2) }}</span>
                @endif
            </div>
            <button wire:click="save" {{ !$this->isBalanced ? 'disabled' : '' }}
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Save Entry
            </button>
        </div>
    </div>

    <!-- Form -->
    <div class="flex-1 overflow-auto p-6">
        @if(session('error'))
            <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white rounded-lg border border-gray-200 p-6 space-y-6">
            <!-- Header Fields -->
            <div class="grid grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Journal *</label>
                    <select wire:model="journalId" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        <option value="">Select journal...</option>
                        @foreach($this->journals as $journal)
                            <option value="{{ $journal->id }}">{{ $journal->code }} - {{ $journal->name }}</option>
                        @endforeach
                    </select>
                    @error('journalId') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date *</label>
                    <input type="date" wire:model="entryDate" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    @error('entryDate') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reference</label>
                    <input type="text" wire:model="reference" placeholder="e.g., INV-001" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <input type="text" wire:model="description" placeholder="Entry description" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>
            </div>

            <!-- Entry Lines -->
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-medium text-gray-700">Entry Lines</h3>
                    <button wire:click="addLine" type="button" class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Line
                    </button>
                </div>

                <div class="border rounded-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-1/3">Account *</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-1/4">Description</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase w-32">Debit</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase w-32">Credit</th>
                                <th class="px-4 py-3 w-10"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($lines as $index => $line)
                                <tr>
                                    <td class="px-4 py-2">
                                        <select wire:model="lines.{{ $index }}.account_id" class="w-full px-2 py-1.5 text-sm rounded border border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                            <option value="">Select account...</option>
                                            @foreach($this->accounts as $account)
                                                <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-4 py-2">
                                        <input type="text" wire:model="lines.{{ $index }}.description" placeholder="Line description" class="w-full px-2 py-1.5 text-sm rounded border border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                    </td>
                                    <td class="px-4 py-2">
                                        <input type="number" step="0.01" min="0" wire:model.live="lines.{{ $index }}.debit" placeholder="0.00" class="w-full px-2 py-1.5 text-sm text-right rounded border border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                    </td>
                                    <td class="px-4 py-2">
                                        <input type="number" step="0.01" min="0" wire:model.live="lines.{{ $index }}.credit" placeholder="0.00" class="w-full px-2 py-1.5 text-sm text-right rounded border border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                    </td>
                                    <td class="px-4 py-2">
                                        @if(count($lines) > 2)
                                            <button wire:click="removeLine({{ $index }})" type="button" class="text-gray-400 hover:text-red-600">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="2" class="px-4 py-3 text-sm font-medium text-gray-700">Totals</td>
                                <td class="px-4 py-3 text-sm text-right font-semibold text-gray-900">
                                    {{ number_format($this->totalDebit, 2) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right font-semibold text-gray-900">
                                    {{ number_format($this->totalCredit, 2) }}
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
