@extends('layouts.thermalprinter') {{-- Use minimal layout for printing --}}

@section('content')

  <h2>🧾 REMITTANCE RECEIPT</h2>

  <div class="info">
    <div>Agent: {{ $collection->agent->name }}</div>
    <div>Date: {{ $collection->collection_date }}</div>
  </div>

  <div class="summary">
    <div class="section-title">SUMMARY</div>
    <div>Total Remitted: ₱{{ number_format($totalAmount, 2) }}</div>
  </div>

  @foreach ($bets as $stubId => $betGroup)
    <div class="stub">
      <div class="section-title">Stub: {{ $stubId }}</div>
      @foreach ($betGroup as $bet)
        <div style="display: flex; justify-content: space-between;">
          <span>{{ $bet->bet_number }}</span>
          <span>₱{{ number_format($bet->amount, 2) }}</span>
        </div>
        <div style="font-size: 11px; margin-left: 10px;">
          {{ strtoupper($bet->game_type) }} • {{ formatDrawTime($bet->game_draw) }}
        </div>
      @endforeach
    </div>
  @endforeach

  <div style="text-align: center; margin-top: 10px;">
    *** END OF RECEIPT ***
  </div>

@endsection
