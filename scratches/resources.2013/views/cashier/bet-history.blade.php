<x-layouts.cashier-sidebar>
  <div class="max-w-md mx-auto p-4 space-y-4">
    <h2 class="text-xl font-semibold">Bet History</h2>

    <form method="GET" action="{{ route('cashier.bet.history') }}" id="dateFilterForm" class="mb-3 text-center">
      <p class="text-xs text-gray-500 mb-1">You can only select dates within the last 3 days.</p>

      <input 
        type="date" 
        name="date" 
        value="{{ $date->toDateString() }}" 
        min="{{ $maxPastDate->toDateString() }}" 
        max="{{ $today->toDateString() }}" 
        onchange="document.getElementById('dateFilterForm').submit()" 
        class="border rounded px-2 py-1"
      >
    </form>

    @forelse ($bets as $bet)
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-3">
        <div class="flex justify-between text-sm text-gray-500 dark:text-gray-300 mb-1">
          <span>{{ $bet->game_draw }} {{ $bet->game_type }}</span>
          <span>{{ \Carbon\Carbon::parse($bet->created_at)->format('h:i:s A') }}</span>
        </div>

        @foreach ($bet->bets as $item)
          <div class="flex justify-between items-center text-sm py-1">
            <div class="flex gap-1">
              @foreach (str_split($item->bet_number) as $digit)
                <div class="w-6 h-6 rounded-full 
                            {{ $item->is_winner ? 'bg-green-300 dark:bg-green-700 text-white font-bold' : 'bg-gray-200 dark:bg-gray-600' }} 
                            flex items-center justify-center text-sm">
                  {{ $digit }}
                </div>
              @endforeach
            </div>
            <div class="text-right">
              <span class="text-gray-700 dark:text-gray-100">(x{{ $item->multiplier ?? '—' }})</span><br>
              ₱{{ number_format($item->amount, 2) }}

              @if($item->is_winner)
                <div class="text-green-600 text-xs font-semibold">🏆 Winner</div>
              @endif
            </div>
          </div>
        @endforeach

        <div class="flex justify-between mt-2 font-semibold text-sm">
          <span class="text-gray-500">TOTAL</span>
          <span class="text-gray-900 dark:text-white">₱{{ number_format($bet->total, 2) }}</span>
        </div>

        <!-- Winning or waiting result notice -->
        @if($bet->winnings !== null)
          <p class="text-sm font-bold text-green-600 mt-1">Winnings: ₱{{ number_format($bet->winnings, 2) }}</p>
        @elseif($bet->total !== null)
          <p class="text-xs text-yellow-500 mt-1">Waiting for result or multiplier...</p>
        @endif

        <div class="text-xs text-gray-400 mt-1">Stub ID: {{ $bet->stub_id }}</div>

        <div class="mt-2 text-right">
          <button class="text-blue-600 text-sm hover:underline">Reprint</button>
        </div>
      </div>
    @empty
      <p class="text-center text-gray-500 dark:text-gray-400 mt-10">No bets found for this date.</p>
    @endforelse
  </div>
</x-layouts.cashier-sidebar>
