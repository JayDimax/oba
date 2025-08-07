<div class="overflow-x-auto bg-white dark:bg-gray-800 rounded shadow border border-gray-200 dark:border-gray-700">
    {{-- resources/views/cashier/reports/partials/winnings.blade.php --}}
    <table class="w-full bg-white dark:bg-gray-800 text-sm shadow rounded">
        <thead class="bg-gray-100 dark:bg-gray-700 text-left text-gray-600 dark:text-gray-300">
            <tr>
                <th class="px-4 py-2">#</th>
                <th class="px-4 py-2">Stub ID</th>
                <th class="px-4 py-2">Game Type</th>
                <th class="px-4 py-2">Bet Amount</th>
                <th class="px-4 py-2">Winnings</th>
                <th class="px-4 py-2">Agent</th>
                <th class="px-4 py-2">Game Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reports as $bet)
                <tr class="border-t dark:border-gray-700">
                    <td class="px-4 py-2">{{ $loop->iteration }}</td>
                    <td class="px-4 py-2">{{ $bet->stub_id }}</td>
                    <td class="px-4 py-2">{{ $bet->game_type }}</td>
                    <td class="px-4 py-2">₱{{ number_format($bet->amount, 2) }}</td>
                    <td class="px-4 py-2 text-green-600 font-bold">₱{{ number_format($bet->winnings, 2) }}</td>
                    <td class="px-4 py-2">{{ $bet->betAgent->name ?? 'N/A' }}</td>
                    <td class="px-4 py-2">{{ \Carbon\Carbon::parse($bet->game_date)->format('M d, Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-2 text-center text-gray-500">No winning bets found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>


</div>
