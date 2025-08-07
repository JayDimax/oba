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
    <h1 class="text-2xl font-bold mb-4 text-gray-800 dark:text-white">🏆 Winning Bets</h1>
    {{-- page content --}}

    {{-- filter --}}
    <div class="flex justify-center mb-1">
        <form method="GET" action="{{ route('agent.winning') }}" class="flex items-center space-x-4" id="winfilter">
            <input
                type="date"
                id="draw_date"
                name="draw_date"
                value="{{ old('draw_date', $drawDate) }}"
                class="border px-3 py-2 rounded dark:bg-gray-700 dark:text-white dark:border-gray-600"
                max="{{ date('Y-m-d') }}"
                onchange="document.getElementById('winfilter').submit()" 
                required
            />
        </form>
    </div>

    <!-- UNCLAIMED WINNINGS -->
    <h3 class="text-lg font-bold text-gray-800 dark:text-white mt-6 mb-2">🎫 Unclaimed Winnings</h3>

    @forelse ($unclaimed as $bet)
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 mb-4">
        <div class="flex justify-between text-sm text-gray-500 dark:text-gray-300 mb-2">
        <span>{{ $bet->game_type }}</span>
        <span>{{ \Carbon\Carbon::parse($bet->created_at)->format('M d, Y h:i A') }}</span>
        </div>

        <div class="text-sm space-y-2">
        <div class="flex justify-between">
            <span class="text-gray-500">Stub ID</span>
            <span class="text-gray-900 dark:text-white">{{ $bet->stub_id ?? ($bet->stub->stub_id ?? 'N/A') }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-gray-500">Amount</span>
            <span class="text-gray-900 dark:text-white">₱{{ number_format($bet->amount, 2) }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-gray-500">Multiplier</span>
            <span class="text-gray-900 dark:text-white">{{ $bet->display_multiplier }}</span>
        </div>

        <div class="flex justify-between">
            <span class="text-gray-500">Total Winnings</span>
            <span class="text-yellow-600 dark:text-yellow-400 font-semibold">
            ₱{{ number_format($bet->winnings, 2) }}
            </span>

        </div>

        <div class="flex justify-between">
            <span class="text-gray-500">Status</span>
            <span class="text-yellow-600 text-xs font-bold">UNCLAIMED</span>
        </div>
        </div>

        <div class="mt-3 text-right">
        <a href="{{ route('agent.receipts.show', $bet->stub_id) }}" target="_blank" class="text-blue-600 text-sm hover:underline">Reprint</a>
        </div>
    </div>
    @empty
    <p class="text-center text-gray-500 dark:text-gray-400 mt-6">No unclaimed winnings for this date.</p>
    @endforelse


    <!-- CLAIMED WINNINGS -->
    <h3 class="text-lg font-bold text-gray-800 dark:text-white mt-8 mb-2">✅ Claimed Winnings</h3>

    @forelse ($claimed as $bet)
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 mb-4">
        <div class="flex justify-between text-sm text-gray-500 dark:text-gray-300 mb-2">
        <span>{{ $bet->game_type }}</span>
        <span>{{ \Carbon\Carbon::parse($bet->claim->claimed_at)->format('M d, Y h:i A') }}</span>
        </div>

        <div class="text-sm space-y-2">
        <div class="flex justify-between">
            <span class="text-gray-500">Stub ID</span>
            <span class="text-gray-900 dark:text-white">{{ $bet->stub_id ?? ($bet->stub->stub_id ?? 'N/A') }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-gray-500">Amount</span>
            <span class="text-gray-900 dark:text-white">₱{{ number_format($bet->amount, 2) }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-gray-500">Multiplier</span>
            <span class="text-gray-900 dark:text-white">{{ $bet->multiplier }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-gray-500">Total Winnings</span>
            <span class="text-yellow-600 dark:text-yellow-400 font-semibold">₱{{ number_format($bet->amount * $bet->multiplier, 2) }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-gray-500">Status</span>
            <span class="text-green-600 text-xs font-bold">CLAIMED</span>
        </div>
        </div>

        <div class="mt-3 text-right">
        <a href="{{ route('agent.receipts.show', $bet->stub_id) }}" target="_blank" class="text-blue-600 text-sm hover:underline">Reprint</a>
        </div>
    </div>
    @empty
    <p class="text-center text-gray-500 dark:text-gray-400 mt-6">No claimed winnings for this date.</p>
    @endforelse

</x-layouts.panel>
