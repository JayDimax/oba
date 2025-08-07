<div class="overflow-x-auto bg-white dark:bg-gray-800 rounded shadow border border-gray-200 dark:border-gray-700">
    <table class="w-full text-sm text-left text-gray-900 dark:text-gray-100">
        <thead class="bg-gray-100 dark:bg-gray-700">
            <tr>
                <th class="p-2">#</th>
                <th class="p-2">Agent</th>
                <th class="p-2">Collection Date</th>
                <th class="p-2">Gross (₱)</th>
                <th class="p-2">Payouts (₱)</th>
                <th class="p-2">Deductions (₱)</th>
                <th class="p-2">Net Remit (₱)</th>
                <th class="p-2">GCash Reference</th>
                <th class="p-2">Proof File</th>
                <th class="p-2">Status</th>
                <th class="p-2">Verified At</th>
                <th class="p-2">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($reports as $i => $collection)
                <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <td class="p-2 font-mono">{{ $reports->firstItem() + $i }}</td>
                    <td class="p-2">{{ $collection->agent?->name ?? 'Unknown' }}</td>
                    <td class="p-2">{{ \Carbon\Carbon::parse($collection->collection_date)->format('M d, Y') }}</td>
                    <td class="p-2">₱{{ number_format($collection->gross ?? 0, 2) }}</td>
                    <td class="p-2">₱{{ number_format($collection->payouts ?? 0, 2) }}</td>
                    <td class="p-2">₱{{ number_format($collection->deductions ?? 0, 2) }}</td>
                    <td class="p-2">₱{{ number_format($collection->net_remit ?? 0, 2) }}</td>
                    <td class="p-2">{{ $collection->gcash_reference ?? '-' }}</td>
                    <td class="p-2">
                        @if ($collection->proof_file)
                            <a href="{{ asset('storage/' . $collection->proof_file) }}" target="_blank" class="text-indigo-600 hover:underline">View</a>
                        @else
                            -
                        @endif
                    </td>
                    <td class="p-2">{{ ucfirst($collection->status ?? 'N/A') }}</td>
                    <td class="p-2">{{ $collection->verified_at ? \Carbon\Carbon::parse($collection->verified_at)->format('M d, Y h:i A') : '-' }}</td>
                    <td class="p-2">{{ $collection->remarks ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center p-4 text-gray-500">No collections found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
