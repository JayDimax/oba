@extends('cashier.reports-template')
@section('report-table')
<table class="w-full text-sm text-left overflow-x-auto">
  <thead class="bg-gray-100">
    <tr>
      <th class="p-2">Agent</th>
      <th class="p-2">Outstanding Balance</th>
      <th class="p-2">Last Updated</th>
      <th class="p-2">Remarks</th>
    </tr>
  </thead>
  <tbody>
    @foreach($balances as $b)
    <tr class="border-b">
      <td class="p-2">{{ $b->agent->name }}</td>
      <td class="p-2">₱{{ number_format($b->balance, 2) }}</td>
      <td class="p-2">{{ $b->last_updated }}</td>
      <td class="p-2">{{ $b->remarks }}</td>
    </tr>
    @endforeach
  </tbody>
</table>
@endsection
