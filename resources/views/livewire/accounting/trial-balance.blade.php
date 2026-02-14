<div class="h-full flex flex-col">
    <!-- Header -->
    <div class="bg-white border-b px-6 py-4 flex items-center justify-between flex-shrink-0">
        <div class="flex items-center gap-4">
            <h1 class="text-xl font-semibold text-gray-900">Trial Balance</h1>
        </div>
        <div class="flex items-center gap-3">
            <div>
                <label class="text-sm text-gray-500 mr-2">As of:</label>
                <input type="date" wire:model.live="asOfDate" class="px-3 py-2 rounded-lg border border-gray-300 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
            </div>
            <div>
                <select wire:model.live="fiscalYearId" class="px-3 py-2 rounded-lg border border-gray-300 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    <option value="">All Periods</option>
                    @foreach($this->fiscalYears as $fy)
                        <option value="{{ $fy->id }}">{{ $fy->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Report -->
    <div class="flex-1 overflow-auto p-6">
        @if(empty($report['accounts']))
            <div class="bg-white rounded-lg border border-gray-200 p-12 text-center text-gray-500">
                No journal entries found for this period.
            </div>
        @else
            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b">
                    <h2 class="text-lg font-medium text-gray-900">Trial Balance as of {{ \Carbon\Carbon::parse($report['as_of_date'])->format('F d, Y') }}</h2>
                </div>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Debit</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Credit</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php
                            $typeColors = [
                                'asset' => 'bg-blue-100 text-blue-800',
                                'liability' => 'bg-red-100 text-red-800',
                                'equity' => 'bg-purple-100 text-purple-800',
                                'revenue' => 'bg-green-100 text-green-800',
                                'expense' => 'bg-orange-100 text-orange-800',
                            ];
                        @endphp
                        @foreach($report['accounts'] as $account)
                            <tr>
                                <td class="px-6 py-3 whitespace-nowrap">
                                    <span class="font-mono text-sm text-gray-900">{{ $account['code'] }}</span>
                                </td>
                                <td class="px-6 py-3 text-sm text-gray-900">{{ $account['name'] }}</td>
                                <td class="px-6 py-3 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $typeColors[$account['type']] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst($account['type']) }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap text-sm text-right font-medium {{ $account['debit'] > 0 ? 'text-gray-900' : 'text-gray-400' }}">
                                    {{ $account['debit'] > 0 ? number_format($account['debit'], 2) : '-' }}
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap text-sm text-right font-medium {{ $account['credit'] > 0 ? 'text-gray-900' : 'text-gray-400' }}">
                                    {{ $account['credit'] > 0 ? number_format($account['credit'], 2) : '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr class="font-semibold">
                            <td colspan="3" class="px-6 py-4 text-sm text-gray-700">Totals</td>
                            <td class="px-6 py-4 text-sm text-right text-gray-900">{{ number_format($report['total_debit'], 2) }}</td>
                            <td class="px-6 py-4 text-sm text-right text-gray-900">{{ number_format($report['total_credit'], 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="5" class="px-6 py-2">
                                @if($report['is_balanced'])
                                    <span class="flex items-center justify-center gap-1 text-sm text-green-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Trial Balance is balanced
                                    </span>
                                @else
                                    <span class="flex items-center justify-center gap-1 text-sm text-red-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                        Trial Balance is NOT balanced - Difference: {{ number_format(abs($report['total_debit'] - $report['total_credit']), 2) }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>
</div>
