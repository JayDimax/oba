@extends('layouts.admin')

@section('title', 'Yearly Reports')

@section('content')
<div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
    <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-4">📅 Yearly Report</h2>

    {{-- Yearly Tab --}}
    @include('admin.reports.report-table', [
        'title' => 'Yearly Report for ' . $filterYear,
        'filterLabel' => 'Select Year',
        'filterName' => 'filter_year',
        'filterValue' => $filterYear,
        'filterType' => 'number', {{-- could also use 'text' with pattern="[0-9]{4}" --}}
        'reportData' => $yearlyData,
        'actionRoute' => route('admin.reports.yearly')
    ])
</div>
@endsection
