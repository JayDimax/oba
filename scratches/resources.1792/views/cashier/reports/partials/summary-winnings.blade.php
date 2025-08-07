<div class="overflow-x-auto bg-white dark:bg-gray-800 rounded shadow border border-gray-200 dark:border-gray-700">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
        <thead class="bg-gray-100 dark:bg-gray-800">
            <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300">Agent</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300">Bet Number</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 dark:text-gray-300">Game Type</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-700 dark:text-gray-300">Amount</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-700 dark:text-gray-300">Payout</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-700 dark:text-gray-300">Date</th>
            </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
            @forelse ($reports as $bet)
                <tr>
                    <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $bet->agent->name ?? 'N/A' }}</td>
                    <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $bet->bet_number }}</td>
                    <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ strtoupper($bet->game_type) }}</td>
                    <td class="px-4 py-2 text-sm text-right text-gray-900 dark:text-gray-100">₱{{ number_format($bet->amount, 2) }}</td>
                    <td class="px-4 py-2 text-sm text-right text-green-600 dark:text-green-400">₱{{ number_format($bet->payout, 2) }}</td>
                    <td class="px-4 py-2 text-sm text-right text-gray-900 dark:text-gray-100">{{ $bet->created_at->format('M d, Y h:i A') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-2 text-center text-sm text-gray-500 dark:text-gray-400">
                        No winning bets found for the selected criteria.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
