@props([
    'title' => 'Report',
    'filterLabel' => 'Filter',
    'filterName' => 'filter',
    'filterValue' => null,
    'filterType' => 'date', // can be 'date', 'month', 'year', or 'text'
    'filterMin' => null,
    'filterMax' => null,
    'filterStep' => null,
    'actionRoute' => null,
    'reportData' => [],
])

<div class="mb-6">
    <h1 class="text-2xl font-bold mb-4">{{ $title }}</h1>

    @if($actionRoute)
    <form method="GET" action="{{ $actionRoute }}" class="mb-4 flex gap-4 items-end">
        <div>
            <label for="{{ $filterName }}" class="block text-sm font-medium text-gray-700">{{ $filterLabel }}</label>
            @if ($filterType === 'month')
                <input type="month" id="{{ $filterName }}" name="{{ $filterName }}" value="{{ $filterValue }}" class="border rounded px-2 py-1" />
            @elseif ($filterType === 'year')
                <input type="number" 
                    id="{{ $filterName }}" 
                    name="{{ $filterName }}" 
                    min="{{ $filterMin ?? 2000 }}" 
                    max="{{ $filterMax ?? date('Y') }}" 
                    step="{{ $filterStep ?? 1 }}" 
                    value="{{ $filterValue }}" 
                    class="border rounded px-2 py-1" />
            @else
                <input type="{{ $filterType }}" id="{{ $filterName }}" name="{{ $filterName }}" value="{{ $filterValue }}" class="border rounded px-2 py-1" />
            @endif
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Filter</button>
    </form>
    @endif

    <table class="min-w-full border border-gray-300">
        <thead class="bg-gray-100">
            <tr>
                <th class="border px-4 py-2 text-left">Agent Name</th>
                <th class="border px-4 py-2 text-right">Gross Sales</th>
                <th class="border px-4 py-2 text-right">Net Remittance</th>
                <th class="border px-4 py-2 text-right">Balance</th>
                <th class="border px-4 py-2 text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($reportData as $data)
                <tr>
                    <td class="border px-4 py-2">{{ $data['agent']->name }}</td>
                    <td class="border px-4 py-2 text-right">{{ number_format($data['gross_sales'], 2) }}</td>
                    <td class="border px-4 py-2 text-right">{{ number_format($data['net_remittance'], 2) }}</td>
                    <td class="border px-4 py-2 text-right {{ $data['difference'] < 0 ? 'text-red-600' : 'text-green-600' }}">
                        {{ number_format($data['difference'], 2) }}
                    </td>
                    <td class="border px-4 py-2 text-center">
                        @if ($data['status'] === 'Balanced')
                            <span class="text-green-600 font-semibold">✅ Balanced</span>
                        @else
                            <span class="text-red-600 font-semibold">⚠️ Under</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="border px-4 py-2 text-center text-gray-500">No data found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
