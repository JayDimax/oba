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
  <h1 class="text-2xl font-bold mb-4 text-gray-800 dark:text-white">📤 Daily Collection</h1>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    <!-- Cashier Card -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 space-y-1">
      <div class="text-xs uppercase text-gray-400">Cashier</div>
      <div class="font-bold text-lg text-gray-800 dark:text-white">{{ $cashier->name ?? 'Unassigned' }}</div>
      <div class="text-gray-500 dark:text-gray-300">{{ $cashier->agent_code ?? 'N/A' }}</div>
      <div class="text-sm text-gray-600 dark:text-gray-300 mt-1 italic">
        Authorized Cashier
      </div>
      {{-- <div class="text-sm text-gray-500 dark:text-gray-400">{{ $cashier->betAgent->phone ?? 'N/A' }}
    </div> --}}
  </div>

  <!-- Agent Card -->
  <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 space-y-1">
    <div class="text-xs uppercase text-gray-400">Collector</div>
    <div class="font-bold text-lg text-gray-800 dark:text-white">{{ $agent->name }}</div>
    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $agent->agent_code ?? 'N/A' }}</div>
    <div class="text-sm text-gray-600 dark:text-gray-300 mt-1 italic">
      Registered Agent
    </div>
  </div>
  </div>

  <!-- Net Remittance Summary Cards Row -->
  <!-- <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-6"> -->

@php
    $isDebit = $netRemitFromUnremitted < 0;
    $displayAmount = number_format(abs($netRemitFromUnremitted), 2);
@endphp

<div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 border-l-4 {{ $isDebit ? 'border-red-600' : 'border-blue-600' }}">
    <div class="text-xs uppercase {{ $isDebit ? 'text-red-600' : 'text-gray-400' }} mb-1 font-semibold">
        {{ $isDebit ? 'Debit to Cashier (Negative Balance)' : 'Net Remittance (Unremitted)' }}  
    </div>
    <div class="text-2xl font-bold {{ $isDebit ? 'text-red-700 dark:text-red-400' : 'text-blue-700 dark:text-blue-300' }}">
        ₱{{ $displayAmount }}
    </div>
    <div class="text-sm text-gray-600 dark:text-gray-300 mt-1 italic">
        Remittance for unsubmitted stubs from draws: 2PM, 5PM, 9PM
    </div>
</div>



  <!-- ✅ Projected Commission for NonWinning Bets -->
  <!-- <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
    <div class="text-xs uppercase text-gray-400 mb-1">Regular Commission</div>
    <div class="text-2xl font-bold text-green-600 dark:text-green-400">
      ₱{{ number_format($projectedCommission, 2) }}
    </div>
    <div class="text-sm text-gray-600 dark:text-gray-300 mt-1 italic">
      10% commission from non-winning bets.
    </div>
  </div> -->

  <!-- 🕒 Projected Commission for Hits -->
  <!-- <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
    <div class="text-xs uppercase text-gray-400 mb-1">Incentives</div>
    <div class="text-2xl font-bold text-blue-700 dark:text-blue-300">
      ₱{{ number_format($incentives, 2) }}
    </div>
    <div class="text-sm text-gray-600 dark:text-gray-300 mt-1 italic">
      Hope for the best.
    </div>
  </div>

</div> -->


  {{-- Feedback Alerts --}}
  @if (session('success'))
  <div
    x-data="{ show: true }"
    x-show="show"
    x-init="setTimeout(() => show = false, 3000)"
    x-transition
    class="bg-green-100 dark:bg-green-800 text-green-800 dark:text-green-100 px-4 py-3 rounded shadow mb-4">
    ✅ {{ session('success') }}
  </div>
  @endif

  @if (session('error'))
  <div
    x-data="{ show: true }"
    x-show="show"
    x-init="setTimeout(() => show = false, 4000)"
    x-transition
    class="bg-red-100 dark:bg-red-800 text-red-800 dark:text-red-100 px-4 py-3 rounded shadow mb-4">
    ⚠️ {{ session('error') }}
  </div>
  @endif



<!-- Agent Remittance Form -->
<div x-data="{ confirm: false }" class="max-w-md mx-auto p-4">
  <div class="bg-white dark:bg-gray-800 shadow-md rounded-xl p-6">
    <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Submit Remittance</h2>

    <!-- Remittance Form -->
    <form id="remitForm"
          method="POST"
          action="{{ route('agent.collections.store') }}"
          enctype="multipart/form-data"
          x-data="{ preview: null, loading: false }"
          x-init="$watch(() => $refs.date.value, async (val) => {
              if (!val) return;
              loading = true;
              const res = await fetch(`{{ route('agent.remit-preview') }}?date=${val}`);
              preview = await res.json();
              loading = false;
          })"
    >
      @csrf

      <!-- Game Date -->
      <div class="mb-4">
        <label for="collection_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
          Game Date
        </label>
        <input type="date"
               name="collection_date"
               id="collection_date"
               x-ref="date"
               value="{{ old('collection_date', $gameDate ?? now()->toDateString()) }}"
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
               required>
      </div>

      <!-- Remittance Preview -->
      <div class="mb-4" x-show="loading" class="text-sm text-gray-500 dark:text-gray-400">
        Loading remittance preview...
      </div>

      <!-- Display Net Remittance Breakdown -->
      <div class="mb-4" x-show="preview && preview.status === 'ok'">
        <div class="bg-green-50 dark:bg-gray-700 border border-green-300 dark:border-gray-600 rounded-md px-4 py-3">
          <p class="text-sm text-gray-800 dark:text-white font-medium mb-2">
            💰 Amount to Remit for <span x-text="$refs.date.value"></span>:
          </p>
          <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-1">
            <li><strong>Gross:</strong> ₱<span x-text="Number(preview.gross).toFixed(2)"></span></li>
            <li><strong>Commission:</strong> ₱<span x-text="Number(preview.commission).toFixed(2)"></span></li>
            <li><strong>Payouts:</strong> ₱<span x-text="Number(preview.payouts).toFixed(2)"></span></li>
            <li><strong>Incentives:</strong> ₱<span x-text="Number(preview.incentives).toFixed(2)"></span></li>
            <li class="text-green-700 dark:text-green-300 font-semibold mt-2">
              Net Remit: ₱<span x-text="Number(preview.net_remit).toFixed(2)"></span>
            </li>
          </ul>
        </div>
      </div>

      <!-- Already Remitted Notice -->
      <div class="mb-4" x-show="preview && preview.status === 'remitted'">
        <div class="bg-yellow-100 dark:bg-yellow-800 border border-yellow-400 text-yellow-800 dark:text-yellow-100 rounded-md px-4 py-3 text-sm">
          ✅ Remittance already submitted for <span x-text="$refs.date.value"></span>.
        </div>
      </div>

      <!-- No Bets Notice -->
      <div class="mb-4" x-show="preview && preview.status === 'empty'">
        <div class="bg-red-100 dark:bg-red-700 border border-red-400 text-red-800 dark:text-red-100 rounded-md px-4 py-3 text-sm">
          ⚠ No bets found for the selected date.
        </div>
      </div>

      <!-- Approved Collections List -->
      @if($approvedCollections->isNotEmpty())
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 mb-6">
          <h3 class="text-sm font-semibold text-gray-700 dark:text-white mb-2">Submitted Remittances</h3>
          <ul>
            @foreach($approvedCollections as $collection)
              <li class="p-4 border-b last:border-0">
                <div class="flex justify-between">
                  <span class="font-semibold">Date:</span>
                  <span>{{ \Carbon\Carbon::parse($collection->collection_date)->format('M d, Y') }}</span>
                </div>
                <div class="flex justify-between">
                  <span>Gross:</span>
                  <span>₱{{ number_format($collection->gross, 2) }}</span>
                </div>
                <div class="flex justify-between text-green-600 font-bold">
                  <span>Net Remit:</span>
                  <span>₱{{ number_format($collection->net_remit, 2) }}</span>
                </div>
              </li>
            @endforeach
          </ul>
        </div>
      @endif

      <!-- Upload Proof -->
      <div class="mb-4">
        <label for="proof_file" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
          Upload Proof of Payment
        </label>
        <input type="file"
               name="proof_file"
               id="proof_file"
               accept="image/*,application/pdf"
               class="mt-1 block w-full text-sm border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
               required>
      </div>

      <!-- Submit Trigger -->
      <div>
        <button type="button"
              @click="confirm = true"
              class="w-full py-2 px-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-md shadow transition">
        Remit Now
      </button>

      </div>
    </form>
  </div>

  <!-- Confirmation Modal -->
  <div x-show="confirm" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 w-full max-w-sm mx-auto" @click.away="confirm = false">
      <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-3">Confirm Remittance</h3>
      <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">
        Are you sure you want to remit now? Once submitted, this will be sent to the cashier for approval.
      </p>
      <div class="flex justify-end space-x-2">
        <button @click="confirm = false"
                class="px-4 py-2 text-sm rounded-md bg-gray-300 dark:bg-gray-700 text-gray-800 dark:text-gray-200 hover:bg-gray-400 dark:hover:bg-gray-600">
          Cancel
        </button>
        <button @click="document.getElementById('remitForm').submit()"
                class="px-4 py-2 text-sm rounded-md bg-blue-600 text-white hover:bg-blue-700">
          Confirm
        </button>
      </div>
    </div>
  </div>
</div>








</x-layouts.panel>