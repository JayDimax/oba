@extends('layouts.admin')

@section('title', 'Daily Reports')

@section('content')
<div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
    <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-4">📅 Daily Report</h2>

    {{-- Daily Tab --}}
    @include('admin.reports.report-table', [
        'title' => 'Daily Report for ' . \Carbon\Carbon::parse($filterDate)->format('F j, Y'),
        'filterLabel' => 'Select Date',
        'filterName' => 'filter_date',
        'filterValue' => $filterDate,
        'filterType' => 'date',
        'reportData' => $reportData,
        'actionRoute' => route('admin.reports.daily')
    ])
</div>
@endsection
