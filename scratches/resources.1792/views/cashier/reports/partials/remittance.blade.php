<div class="overflow-x-auto bg-white dark:bg-gray-800 rounded shadow border border-gray-200 dark:border-gray-700">
    <table class="w-full text-sm text-left text-gray-900 dark:text-gray-100">
        <thead class="bg-gray-100 dark:bg-gray-700">
            <tr>
                <th class="p-2">#</th>
                <th class="p-2">Agent</th>
                <!-- <th class="p-2">Total Bets (₱)</th> -->
                <th class="p-2">Gross Sales (₱)</th>
                <!-- <th class="p-2">Commission (₱)</th>
                <th class="p-2">Incentives (₱)</th>
                <th class="p-2">Payouts (₱)</th>
                <th class="p-2">Deductions (₱)</th> -->
                <th class="p-2">Remitted (₱)</th>
                <!-- <th class="p-2">Net to Remit (₱)</th> -->
                <th class="p-2">Recorded At</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($reports as $i => $remit)
                <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <td class="p-2 font-mono">{{ $reports->firstItem() + $i }}</td>
                    <td class="p-2">{{ $agents->firstWhere('id', $remit->agent_id)?->name ?? 'Unknown' }}</td>
                    <!-- <td class="p-2">₱{{ number_format($remit->total_bets ?? 0, 2) }}</td> -->
                    <td class="p-2">₱{{ number_format($remit->gross_sales ?? 0, 2) }}</td>
                    <!-- <td class="p-2">₱{{ number_format($remit->commission ?? 0, 2) }}</td>
                    <td class="p-2">₱{{ number_format($remit->incentives ?? 0, 2) }}</td>
                    <td class="p-2">₱{{ number_format($remit->payouts ?? 0, 2) }}</td>
                    <td class="p-2">₱{{ number_format($remit->deductions ?? 0, 2) }}</td> -->
                    <td class="p-2">₱{{ number_format($remit->remitted ?? 0, 2) }}</td>
                    <!-- <td class="p-2">₱{{ number_format($remit->net_to_remit ?? 0, 2) }}</td> -->
                    <td class="p-2">{{ \Carbon\Carbon::parse($remit->created_at)->format('M d, Y h:i A') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center p-4 text-gray-500">No remittance records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
