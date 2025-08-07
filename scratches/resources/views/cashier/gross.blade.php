@extends('cashier.reports-template')
@section('report-table')
<table class="w-full text-sm text-left overflow-x-auto">
  <thead class="bg-gray-100">
    <tr>
      <th class="p-2">Agent</th>
      <th class="p-2">Gross Bets</th>
      <th class="p-2">Number of Bets</th>
      <th class="p-2">Game Date</th>
    </tr>
  </thead>
  <tbody>
    @foreach($grosses as $g)
    <tr class="border-b">
      <td class="p-2">{{ $g->agent->name }}</td>
      <td class="p-2">₱{{ number_format($g->total_gross, 2) }}</td>
      <td class="p-2">{{ $g->total_bets }}</td>
      <td class="p-2">{{ $date }}</td>
    </tr>
    @endforeach
  </tbody>
</table>
@endsection