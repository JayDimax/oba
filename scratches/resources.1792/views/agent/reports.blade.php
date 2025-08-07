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
         transition-all duration-300 ease-in-out flex flex-col w-72 items-center">
  <!-- Sidebar content (left unchanged) -->
</aside>

 
<x-layouts.panel>
  <x-slot name="sidebar">
    @include('partials.agent-sidebar')
  </x-slot>

  <h1 class="text-2xl font-bold mb-4 text-gray-800 dark:text-white">📊 Reports</h1>

  <div class="min-h-screen px-4 py-6 dark:bg-gray-900 space-y-6">
    <div class="max-w-5xl mx-auto p-4" x-data="{ tab: '{{ $draw ?? 'all' }}' }">


      <div class="flex justify-center mb-4">
        <form method="GET" action="{{ route('agent.reports') }}" class="flex items-center gap-2">
          <input
            type="date"
            name="draw_date"
            value="{{ $date }}"
            max="{{ date('Y-m-d') }}"
            class="border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-800 dark:text-white px-3 py-2 rounded shadow-sm focus:outline-none focus:ring focus:border-blue-400 dark:focus:border-blue-500 transition"
            onchange="this.form.submit()" />
          <input type="hidden" name="draw" :value="tab" />
        </form>
      </div>



      @php
      if (!function_exists('formatDrawTime')) {
      function formatDrawTime($code) {
      return match ($code) {
      '14', '1st' => '2PM',
      '17', '2nd' => '5PM',
      '21', '3rd' => '9PM',
      default => $code,
      };
      }
      }
      @endphp


      <!-- Tabs -->
      <div class="flex justify-center mb-4">
        <div class="grid grid-cols-4 w-full max-w-2xl bg-gray-200 dark:bg-gray-700 rounded-md overflow-hidden">
          @foreach(['all' => 'All', '1st' => '1st Draw', '2nd' => '2nd Draw', '3rd' => '3rd Draw'] as $key => $label)
          @php $active = ($draw === $key || ($key === 'all' && $draw === null)); @endphp
          <a
            href="{{ route('agent.reports', ['draw_date' => $date, 'draw' => $key === 'all' ? null : $key]) }}"
            class="text-center px-2 py-2 text-sm font-medium transition
                    {{ $active 
                          ? 'bg-blue-600 text-white' 
                          : 'text-gray-700 dark:text-white hover:bg-gray-300 dark:hover:bg-gray-600' }}">
            {{ $label }}
          </a>
          @endforeach
        </div>
      </div>




      <!-- Summary -->
      <div class="bg-gray-50 dark:bg-gray-800 rounded-lg shadow px-4 py-3 min-h-32 flex flex-col justify-between">
        <h2 class="text-lg font-semibold text-gray-700 dark:text-white">
          Summary
          <span class="text-xs text-gray-500">
            ({{ $draw === '1st' ? '1st Draw' : ($draw === '2nd' ? '2nd Draw' : ($draw === '3rd' ? '3rd Draw' : 'All')) }})
          </span>
        </h2>


        <div class="space-y-1 text-sm">
          <div class="flex justify-between"><span>Gross Sales:</span><span>₱{{ number_format($summary['gross'], 2) }}</span></div>
          <div class="flex justify-between"><span>Hits:</span><span>₱{{ number_format($summary['hits'], 2) }}</span></div>
          <div class="flex justify-between"><span>Net Sales:</span><span>₱{{ number_format($summary['net_sales'], 2) }}</span></div>
          <div class="flex justify-between"><span>Payout Coverage (Tapada):</span><span>₱{{ number_format($summary['payouts'], 2) }}</span></div>
          <div class="flex justify-between"><span>Commission (10%):</span><span>₱{{ number_format($summary['commission_base'], 2) }}</span></div>
          <div class="flex justify-between"><span>Incentives:</span><span>₱{{ number_format($summary['incentives'], 2) }}</span></div>
          @if ($draw === null || $draw === 'all')
          <div class="flex justify-between">
            <span>Deductions:</span>
            <span>₱{{ number_format($summary['deductions'], 2) }}</span>
          </div>
          @endif

        </div>

        <hr class="my-2 border-gray-300 dark:border-gray-700">

        <!-- Remittance -->
        <div class="flex justify-between font-bold text-blue-600">
          <div>
            <span class="text-gray-700 dark:text-gray-300 text-sm">
              Total Net (Remittance)
            </span>
            <br>
          </div>
          <span class="text-right text-red-600 italic">
            ₱{{ number_format($summary['net_after_payouts'], 2) }}
          </span>
        </div>


        <!-- Print Link -->
        <a 
          href="{{ route('agent.receipt.summary', ['draw_date' => $date, 'draw' => $draw]) }}" 
          class="inline-flex items-center text-sm text-blue-600 hover:underline font-medium"
          target="_blank"
        >
          🖨️ Print
        </a>


        </div>
      </div>

    </div>

    <!-- card loop display -->

    @php
    $drawLabels = [
    '14' => ['label' => '1st Draw - 2PM', 'game' => 'L2'],
    '17' => ['label' => '2nd Draw - 5PM', 'game' => 'S3'],
    '21' => ['label' => '3rd Draw - 9PM', 'game' => '4D'],
    ];

    // Filter the codes to show based on tab
    $tabDraws = match($draw) {
    '1st' => ['14'],
    '2nd' => ['17'],
    '3rd' => ['21'],
    default => ['14', '17', '21'], // all tab
    };
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 mt-6">
      @foreach ($tabDraws as $code)
      <div class="bg-gray-50 dark:bg-gray-800 rounded-lg shadow px-4 py-3 min-h-32 flex flex-col justify-between">
        <div>
          <div class="text-sm text-gray-500 dark:text-gray-300 font-medium flex items-center justify-between mb-2">
            <span>{{ $drawLabels[$code]['game'] }}</span>
            <span class="text-xs text-gray-400">{{ $drawLabels[$code]['label'] }}</span>
          </div>
          <span class="text-xs font-normal text-gray-400 ml-1">Gross</span>
          <div class="text-xl font-bold text-green-600">
            {{ number_format($perDrawStats[$code]['gross'] ?? 0, 2) }}
          </div>
        </div>

        <div class="text-sm mt-3 text-gray-500 dark:text-gray-300 flex justify-between">
          <span>Hits</span>
          <span class="font-medium text-black dark:text-white">
            {{ number_format($perDrawStats[$code]['hits'] ?? 0, 2) }}
          </span>
        </div>
      </div>
      @endforeach
    </div>




  </div>
  </div>



</x-layouts.panel>