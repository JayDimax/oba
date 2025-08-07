@extends('cashier.reports-template')
@section('report-table')
<table class="w-full text-sm text-left overflow-x-auto">
  <thead class="bg-gray-100">
    <tr>
      <th class="p-2">Stub ID</th>
      <th class="p-2">Agent</th>
      <th class="p-2">Bet Amount</th>
      <th class="p-2">Winning Amount</th>
      <th class="p-2">Game Date</th>
      <th class="p-2">Draw Time</th>
      <th class="p-2">Claimed At</th>
    </tr>
  </thead>
  <tbody>
    @foreach($bets as $bet)
    <tr class="border-b">
      <td class="p-2">{{ $bet->stub_id }}</td>
      <td class="p-2">{{ $bet->agent->name }}</td>
      <td class="p-2">₱{{ number_format($bet->amount, 2) }}</td>
      <td class="p-2">₱{{ number_format($bet->winning_amount, 2) }}</td>
      <td class="p-2">{{ $bet->game_date }}</td>
      <td class="p-2">{{ $bet->draw_time }}</td>
      <td class="p-2">{{ $bet->claimed_at }}</td>
    </tr>
    @endforeach
  </tbody>
</table>
@endsection