@extends('layouts.admin')

@section('title', 'Monthly Reports')

@section('content')
<div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
    <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-4">📅 Monthly Report</h2>

    {{-- Monthly Tab --}}
    @include('admin.reports.report-table', [
        'title' => 'Monthly Report for ' . \Carbon\Carbon::parse($filterMonth)->format('F Y'),
        'filterLabel' => 'Select Month',
        'filterName' => 'filter_month',
        'filterValue' => $filterMonth,
        'filterType' => 'month',
        'reportData' => $monthlyData,
        'actionRoute' => route('admin.reports.monthly')
    ])
</div>
@endsection
