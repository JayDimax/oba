<div class="overflow-x-auto bg-white dark:bg-gray-800 rounded shadow border border-gray-200 dark:border-gray-700">
    <table class="w-full text-sm text-left text-gray-900 dark:text-gray-100">
        <thead class="bg-gray-100 dark:bg-gray-700">
            <tr>
                <th class="p-2">#</th>
                <th class="p-2">Agent</th>
                <th class="p-2">Balance (₱)</th>
                <th class="p-2">Type</th>
                <th class="p-2">Note</th>
                <th class="p-2">Remittance Batch</th>
                <th class="p-2">Recorded At</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($reports as $i => $balance)
                <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <td class="p-2 font-mono">{{ $reports->firstItem() + $i }}</td>
                    <td class="p-2">{{ $agents->firstWhere('id', $balance->agent_id)?->name ?? 'Unknown' }}</td>
                    <td class="p-2">₱{{ number_format($balance->amount ?? 0, 2) }}</td>
                    <td class="p-2">{{ ucfirst($balance->type ?? 'N/A') }}</td>
                    <td class="p-2">{{ $balance->note ?? '-' }}</td>
                    <td class="p-2">{{ $balance->remittance_batch_id ?? '—' }}</td>
                    <td class="p-2">{{ \Carbon\Carbon::parse($balance->created_at)->format('M d, Y h:i A') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center p-4 text-gray-500">No agent balances found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
