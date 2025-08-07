<aside
  x-cloak
  :class="{
    'translate-x-0': sidebarOpen || window.innerWidth >= 640,
    '-translate-x-full': !sidebarOpen && window.innerWidth < 640,
    'w-16 sm:w-20': sidebarCollapsed,
    'w-72': !sidebarCollapsed
  }"
  class="sticky bottom-0 left-0 transform
         bg-white dark:bg-gray-800 shadow-md
         transition-all duration-300 ease-in-out flex flex-col w-72 items-center"
>
<!-- Sidebar content (left unchanged) -->
</aside>

<x-layouts.panel>
    <x-slot name="sidebar">
      @include('partials.agent-sidebar')
    </x-slot>

{{-- page title --}}
<h1 class="text-2xl font-bold mb-4 text-gray-800 dark:text-white">🕘 Bet History</h2>
    {{-- page content --}}
  <div class="min-h-screen x-cloak dark:bg-gray-900 p-4 flex flex-col space-y-6">
        <form method="GET" action="{{ route('agent.bet.history') }}" id="dateFilterForm" class="mb-3 text-center">
          <p class="text-xs text-gray-500 mb-1">You can only select dates within the last 3 days.</p>

         <input 
            type="date" 
            name="date" 
            value="{{ $date->toDateString() }}" 
            min="{{ $maxPastDate->toDateString() }}" 
            max="{{ $today->toDateString() }}" 
            onchange="document.getElementById('dateFilterForm').submit()" 
            class="border rounded px-2 py-1 bg-white text-gray-900 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600"
          />

        </form>
        {{ $bets->links() }}
        @forelse ($bets as $bet)
          <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-3">
            <div class="flex justify-between text-sm text-gray-500 dark:text-gray-300 mb-1">
              <span>{{ formatDrawTime($bet->game_draw) }} • {{ $bet->game_type }}</span>
              <span>{{ \Carbon\Carbon::parse($bet->created_at)->format('h:i:s A') }}</span>
            </div>

            @foreach ($bet->bets as $item)
              @php
                $multiplier = $item->multiplier ?? 1;
                $winnings = $item->is_winner ? $item->amount * $multiplier : 0;
              @endphp
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
                <div class="text-right leading-tight">
                  <span class="text-gray-700 dark:text-gray-100 text-xs">(x{{ $multiplier }})</span><br>
                  ₱{{ number_format($item->amount, 2) }}

                  @if($item->is_winner)
                    <div class="text-green-600 text-xs font-semibold">🏆 Winner</div>
                    <div class="text-xs text-green-600">+₱{{ number_format($winnings, 2) }}</div>
                  @endif
                </div>
              </div>
            @endforeach

            <div class="flex justify-between mt-2 font-semibold text-sm">
              <span class="text-gray-500">TOTAL</span>
              <span class="text-gray-900 dark:text-white">₱{{ number_format($bet->total ?? 0, 2) }}</span>
            </div>

            @if($bet->winnings !== null)
              <p class="text-sm font-bold text-green-600 mt-1">Winnings: ₱{{ number_format($bet->winnings, 2) }}</p>
            @elseif($bet->total !== null)
              <p class="text-xs text-yellow-500 mt-1">Waiting for result or multiplier...</p>
            @endif

            <div class="text-xs text-gray-400 mt-1">Stub ID: {{ $bet->stub_id }}</div>

            <div class="mt-2 text-right">
              <a href="{{ route('agent.receipts.show', $bet->stub_id) }}" target="_blank" class="text-blue-600 text-sm hover:underline">Reprint</a>
            </div>
          </div>
        @empty
          <p class="text-center text-gray-500 dark:text-gray-400 mt-10">No bets found for this date.</p>
        @endforelse
          
  </div>
</x-layouts.panel>

@php
  function formatDrawTime($drawCode) {
    return match ($drawCode) {
      '14' => '2PM',
      '17' => '5PM',
      '21' => '9PM',
      default => $drawCode,
    };
  }
@endphp
