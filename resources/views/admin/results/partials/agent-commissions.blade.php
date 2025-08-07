<form method="POST" action="{{ route('admin.agent-commissions.update') }}" class="space-y-4">
    @csrf
    <h3 class="text-lg font-semibold mb-2 text-gray-800 dark:text-white">🧮 Agent Commission Rates by Game Type</h3>

    <div class="overflow-x-auto">
        <table class="w-full border border-gray-300 dark:border-gray-700 rounded overflow-hidden">
            <thead class="bg-gray-100 dark:bg-gray-700 text-left text-sm text-gray-700 dark:text-gray-200">
                <tr>
                    <th class="p-2">Agent Name</th>
                    <th class="p-2">L2 (%)</th>
                    <th class="p-2">S3 (%)</th>
                    <th class="p-2">4D (%)</th>
                    <th class="p-2 text-center">Update</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                @foreach ($commissionAgents as $agent)
                    <tr>
                        <td class="p-2 font-medium text-gray-800 dark:text-gray-100">{{ $agent->name }}</td>
                        @foreach (['L2', 'S3', '4D'] as $gameType)
                            @php
                                $commission = $agent->commissions->firstWhere('game_type', $gameType);
                            @endphp
                            <td class="p-2">
                                <input 
                                    type="number" 
                                    step="0.01" 
                                    min="0" 
                                    max="100"
                                    name="commissions[{{ $agent->id }}][{{ $gameType }}]" 
                                    value="{{ $commission->commission_percent ?? '' }}" 
                                    class="border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-2 py-1 rounded w-full focus:outline-none focus:ring focus:ring-blue-500"
                                >
                            </td>
                        @endforeach
                        <td class="p-2 text-center">
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm transition">
                                💾 Save
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</form>
