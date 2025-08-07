<h3 class="text-lg font-semibold mb-3">📋 Declared Results History</h3>

@if(session('success'))
    <div class="mb-4 text-green-600 font-semibold">{{ session('success') }}</div>
@endif

<table class="w-full border rounded overflow-hidden mb-6 text-sm">
    <thead class="bg-gray-100 text-left">
        <tr>
            <th class="p-2">Game Type</th>
            <th class="p-2">Draw Time</th>
            <th class="p-2">Game Date</th>
            <th class="p-2">Winning Combo</th>
            <th class="p-2">Multiplier</th>
            <th class="p-2">Bonus per ₱10</th>
        </tr>
    </thead>
    <tbody class="bg-white divide-y">
        @foreach ($results as $result)
        <tr>
            <td class="p-2 font-semibold">{{ $result->game_type }}</td>
            <td class="p-2">{{ \Carbon\Carbon::createFromFormat('H:i', $result->game_draw)->format('g:i A') }}</td>
            <td class="p-2">{{ \Carbon\Carbon::parse($result->game_date)->toFormattedDateString() }}</td>
            <td class="p-2 font-mono">{{ $result->winning_combination }}</td>
            <td class="p-2">{{ $result->multiplier ?? '—' }}</td>
            <td class="p-2">{{ $result->bonus_per_10 ?? '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<!-- Pagination -->
<div class="mt-4">
    {{ $results->links() }}
</div>
