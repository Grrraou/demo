<div class="h-full flex flex-col">
    <!-- Header -->
    <div class="bg-white border-b px-6 py-4 flex items-center justify-between flex-shrink-0">
        <div class="flex items-center gap-4">
            <h1 class="text-xl font-semibold text-gray-900">General Ledger</h1>
        </div>
        <div class="flex items-center gap-3">
            <div>
                <select wire:model.live="accountId" class="px-3 py-2 rounded-lg border border-gray-300 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 w-64">
                    <option value="">Select Account...</option>
                    @foreach($this->accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-sm text-gray-500 mr-2">From:</label>
                <input type="date" wire:model.live="dateFrom" class="px-3 py-2 rounded-lg border border-gray-300 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            </div>
            <div>
                <label class="text-sm text-gray-500 mr-2">To:</label>
                <input type="date" wire:model.live="dateTo" class="px-3 py-2 rounded-lg border border-gray-300 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            </div>
        </div>
    </div>

    <!-- Report -->
    <div class="flex-1 overflow-auto p-6">
        @if(!$accountId)
            <div class="bg-white rounded-lg border border-gray-200 p-12 text-center text-gray-500">
                Select an account to view its ledger.
            </div>
        @elseif(empty($entries))
            <div class="bg-white rounded-lg border border-gray-200 p-12 text-center text-gray-500">
                No transactions found for this period.
            </div>
        @else
            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b">
                    <h2 class="text-lg font-medium text-gray-900">
                        {{ $selectedAccount['code'] }} - {{ $selectedAccount['name'] }}
                    </h2>
                    <p class="text-sm text-gray-500">
                        {{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}
                    </p>
                </div>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Entry #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Debit</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Credit</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($entries as $entry)
                            <tr class="{{ $entry['entry_number'] ? '' : 'bg-gray-50 font-medium' }}">
                                <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500">
                                    {{ $entry['entry_date'] ? \Carbon\Carbon::parse($entry['entry_date'])->format('M d, Y') : '' }}
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap">
                                    @if($entry['entry_number'])
                                        <span class="font-mono text-sm text-gray-900">{{ $entry['entry_number'] }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500">
                                    {{ $entry['reference'] ?? '-' }}
                                </td>
                                <td class="px-6 py-3 text-sm text-gray-900">
                                    {{ $entry['description'] ?? '-' }}
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap text-sm text-right {{ $entry['debit'] ? 'text-gray-900' : 'text-gray-400' }}">
                                    {{ $entry['debit'] ? number_format($entry['debit'], 2) : '-' }}
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap text-sm text-right {{ $entry['credit'] ? 'text-gray-900' : 'text-gray-400' }}">
                                    {{ $entry['credit'] ? number_format($entry['credit'], 2) : '-' }}
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap text-sm text-right font-medium {{ $entry['balance'] >= 0 ? 'text-gray-900' : 'text-red-600' }}">
                                    {{ number_format($entry['balance'], 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
