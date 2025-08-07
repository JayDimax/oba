@extends('layouts.cashier')

@section('title', 'Reports')

@section('content')
<div class="bg-white dark:bg-gray-900 shadow rounded p-6">
    <h2 class="text-xl font-semibold mb-4 text-gray-800 dark:text-gray-100">Reports</h2>
<!-- FILTER -->
    <div class="flex justify-center mb-4">
        <div class="relative w-[250px]">
            <input type="date" x-model="selectedDate"
            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring focus:ring-blue-200">
            <span class="absolute right-3 top-2.5 text-gray-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8 7V3m8 4V3M3 11h18M5 5h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z" />
            </svg>
            </span>
        </div>
    </div>
<!-- DATA -->
    <div class="overflow-x-auto bg-white shadow-md rounded-lg p-4">
    <table class="w-full text-sm text-left">
        <thead class="bg-gray-100 uppercase text-gray-600">
        <tr>
            <th class="p-2">Agent</th>
            <th class="p-2">Gross</th>
            <th class="p-2">Net</th>
            <th class="p-2">Remitted</th>
            <th class="p-2">Status</th>
        </tr>
        </thead>
        <tbody>
        @foreach($remittances as $r)
        <tr class="border-b">
            <td class="p-2">{{ $r->agent->name }}</td>
            <td class="p-2">₱{{ number_format($r->gross, 2) }}</td>
            <td class="p-2">₱{{ number_format($r->net, 2) }}</td>
            <td class="p-2">₱{{ number_format($r->amount_remitted, 2) }}</td>
            <td class="p-2">{{ $r->status }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>
    </div>
    <!-- PRINT BUTTON -->
    <div class="mt-4 flex justify-end print:hidden">
    <button onclick="window.print()"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Print Report</button>
    </div>

</div>
@endsection
