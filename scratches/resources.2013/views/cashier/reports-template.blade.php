@extends('layouts.cashier')

@section('title', 'Reports')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold mb-2">Reports</h2>

    <!-- Filter Form -->
    <form method="GET" class="flex flex-wrap gap-4 items-end mb-4" id="filterForm">
        <input type="hidden" name="report_type" value="{{ $reportType }}">

        {{-- Agent Filter --}}
        <div>
            <label for="agent_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Agent</label>
            <select name="agent_id" id="agent_id" class="border px-3 py-1 rounded dark:bg-gray-800 dark:text-white">
                <option value="">All</option>
                @foreach ($agents as $agent)
                    <option value="{{ $agent->id }}" {{ $selectedAgentId == $agent->id ? 'selected' : '' }}>
                        {{ $agent->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Date Filter --}}
        <div>
            <label for="filter_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date</label>
            <input type="date" name="filter_date" id="filter_date" value="{{ $filterDate }}"
                   class="border px-3 py-1 rounded dark:bg-gray-800 dark:text-white">
        </div>

        <div>
            <button type="submit" 
                    class="bg-blue-600 text-white px-4 py-1.5 rounded hover:bg-blue-700">
                Filter
            </button>
        </div>
    </form>

    <!-- Tab Switch -->
    <div class="flex flex-wrap gap-2 mb-4 items-center">
        @foreach (['collections','winnings', 'balances'] as $type)
            <a href="{{ route('cashier.reports', array_merge(request()->except('page'), ['report_type' => $type])) }}"
               class="px-4 py-2 rounded border 
                      {{ $reportType === $type 
                            ? 'bg-blue-600 text-white border-blue-600' 
                            : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-white border-transparent' }}">
                {{ ucfirst($type) }}
            </a>
        @endforeach
 
        <!-- Print Button -->
        <a href="{{ route('cashier.report.print') }}" 
            target="_blank" 
            class="ml-auto px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 print:hidden">
                🖨️ Print
            </a>
    </div>

    <!-- Summary Display (conditionally included based on report type) -->
    <!-- @if (!empty($summary) && is_array($summary))
        <div class="bg-gray-100 dark:bg-gray-700 p-4 rounded shadow mb-6 max-w-md mx-auto">
            <h3 class="text-lg font-semibold mb-2 text-center">Summary</h3>
            <table class="w-full text-right">
                @foreach ($summary as $key => $value)
                    <tr>
                        <th class="text-left capitalize pr-4">{{ str_replace('_', ' ', $key) }}:</th>
                        <td>₱{{ number_format($value, 2) }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    @endif -->

    <!-- Report Table -->
    @includeIf('cashier.reports.partials.' . $reportType, ['reports' => $reports, 'agents' => $agents])

    <!-- Pagination -->
    <div class="mt-4">
        {{ $reports->withQueryString()->links() }}
    </div>
</div>
@endsection
