<form method="POST" action="{{ route('admin.multipliers.update') }}" class="space-y-4">
    @csrf
    <h3 class="text-lg font-semibold mb-2">🎯 Game Type Multipliers & Bonus Settings</h3>

    <table class="w-full border rounded overflow-hidden">
        <thead class="bg-gray-100 text-left">
            <tr>
                <th class="p-2">Game Type</th>
                <th class="p-2">Multiplier</th>
                <th class="p-2">Bonus per ₱10</th>
                <th class="p-2 text-center">Update</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y">
            @foreach($gameSettings as $setting)
            <tr>
                <td class="p-2 font-medium">{{ $setting->game_type }}</td>
                <td class="p-2">
                    <input type="number" step="0.01" min="1" name="settings[{{ $setting->id }}][multiplier]" value="{{ $setting->multiplier }}" class="border px-2 py-1 rounded w-full">
                </td>
                <td class="p-2">
                    <input type="number" step="0.01" min="0" name="settings[{{ $setting->id }}][bonus_per_10]" value="{{ $setting->bonus_per_10 }}" class="border px-2 py-1 rounded w-full">
                </td>
                <td class="p-2 text-center">
                    <input type="hidden" name="settings[{{ $setting->id }}][game_type]" value="{{ $setting->game_type }}">
                    <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 text-sm">💾 Save</button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</form>
