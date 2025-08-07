<div class="overflow-x-auto bg-white dark:bg-gray-800 rounded shadow border border-gray-200 dark:border-gray-700">
    <table class="w-full text-sm text-left text-gray-900 dark:text-gray-100">
        <thead class="bg-gray-100 dark:bg-gray-700">
            <tr>
                <th class="p-2">#</th>
                <th class="p-2">Stub ID</th>
                <th class="p-2">Game Type</th>
                <th class="p-2">Game Draw</th>
                <th class="p-2">Game Date</th>
                <th class="p-2">Bet Number</th>
                <th class="p-2">Amount (₱)</th>
                <th class="p-2">Is Winner</th>
                <th class="p-2">Winnings (₱)</th>
                <th class="p-2">Created At</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($reports as $i => $bet)
                <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <td class="p-2 font-mono">{{ $reports->firstItem() + $i }}</td>
                    <td class="p-2 font-mono">{{ $bet->stub_id }}</td>
                    <td class="p-2">{{ ucfirst($bet->game_type) }}</td>
                    <td class="p-2">{{ $bet->game_draw }}</td>
                    <td class="p-2">{{ \Carbon\Carbon::parse($bet->game_date)->format('M d, Y') }}</td>
                    <td class="p-2">{{ $bet->bet_number }}</td>
                    <td class="p-2">₱{{ number_format($bet->amount, 2) }}</td>
                    <td class="p-2">{{ $bet->is_winner ? 'Yes' : 'No' }}</td>
                    <td class="p-2">₱{{ number_format($bet->winnings ?? 0, 2) }}</td>
                    <td class="p-2">{{ \Carbon\Carbon::parse($bet->created_at)->format('M d, Y h:i A') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center p-4 text-gray-500">No winnings found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
