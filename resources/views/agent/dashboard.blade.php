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

<!-- Main content -->
<x-layouts.panel>
  <x-slot name="sidebar">
    @include('partials.agent-sidebar')
  </x-slot>

  <div class="min-h-screen x-cloak dark:bg-gray-900 p-4 flex flex-col space-y-6">
    <!-- Header -->
    <div class="mb-6 mt-4" x-data="{ showAnnouncement: true }" x-cloak>
    <div x-show="showAnnouncement"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform scale-90"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-90"
         class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow-md flex items-start justify-between space-x-4 border border-gray-200 dark:border-gray-700">

        <div class="flex items-start space-x-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-megaphone text-blue-600 dark:text-blue-400 flex-shrink-0 mt-1">
                <path d="m3 11 18-5L11 22V11z"/>
                <path d="m22 2L11 22"/>
            </svg>

            <div>
                <h3 class="font-semibold text-lg text-gray-900 dark:text-white">Important Announcement!</h3>
                <p class="text-sm text-gray-600 dark:text-gray-300">New features, game updates, and important information will be posted here. Stay tuned for the latest news!</p>
            </div>
        </div>

        <button @click="showAnnouncement = false"
                class="text-gray-400 hover:text-gray-600 dark:hover:text-white transition-colors duration-200 flex-shrink-0 mt-1">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
</div>

    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-4xl font-extrabold text-gray-900 dark:text-white" style="font-size: 2rem;">ORCAS</h1>
        <p class="text-xs text-gray-600 dark:text-gray-300 mb-6">Betting App</p>
        <span class="text-sm">Agent: {{Auth::user()->name}}</span>
      </div>
      <img src="{{ asset('images/orca-logo.png') }}" alt="ORCAS Logo" class="h-12 w-auto mb-6">
    </div>

        <!-- SECTION 2: Today Gross & Add Bet -->
      <div 
          class="bg-white  bg-gray-800 shadow rounded-lg p-3 w-full dark:bg-gray-800 rounded-xl p-4  flex justify-between items-center"
          style="min-height: 200px;"
        >  
        <!-- Left side: vertically centered -->
        <div class="flex flex-col justify-center">
          <p class="text-md text-gray-600 dark:text-gray-300">Today Gross</p>
          <div class="flex items-end mt-2 mb-2">
            <span class="font-bold tracking-tight text-gray-900 dark:text-white text-4xl" style="font-size: 3rem;">
              {{ number_format($todayGross, 2) }}
            </span>

          </div>
        </div>

        <!-- Right side: button aligned top right, no stretch -->
        <button
          id="open-bet-modal"
          class="py-2 px-4 text-base font-semibold bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition self-start whitespace-nowrap"
          style="align-self: flex-start;">
          Add Bet
        </button>
      </div>

    <!-- SECTION 3: Navigation Buttons (One Row, Equal Width) -->
    <div class="flex flex-wrap justify-between gap-2 sm:gap-4 mt-4 w-full ">

      <!-- History -->
      <a href="{{route('agent.bet.history')}}" class="group flex-1 min-w-[70px] py-3 px-4 text-sm font-semibold bg-white bg-gray-800 shadow rounded-lg p-3 dark:bg-gray-800 rounded-xl 
                    transition duration-150 ease-in-out hover:border-blue-500 active:border-blue-700 focus:outline-none 
                    focus:ring focus:ring-blue-200 flex flex-col items-center justify-center">
        <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"
            class="lucide lucide-history group-hover:text-blue-600 text-gray-600 dark:text-white mb-1 w-12 h-12">
          <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
          <path d="M3 3v5h5"/>
          <path d="M12 7v5l4 2"/>
        </svg>
        <span class="text-xs font-medium mt-1 text-gray-500 dark:text-gray-400 group-hover:text-blue-600">History</span>
      </a>

      <!-- Winnings -->
      <a href="{{route('agent.winning')}}" class="group flex-1 min-w-[70px] py-3 px-4 text-sm font-semibold bg-white bg-gray-800 shadow rounded-lg p-3 dark:bg-gray-800 rounded-xl 
                    transition duration-150 ease-in-out hover:border-blue-500 active:border-blue-700 focus:outline-none 
                    focus:ring focus:ring-blue-200 flex flex-col items-center justify-center">
        <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"
            class="lucide lucide-sparkle group-hover:text-blue-600 text-gray-600 dark:text-white mb-1 w-12 h-12">
          <path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"/>
        </svg>
        <span class="text-xs font-medium mt-1 text-gray-500 dark:text-gray-400 group-hover:text-blue-600">Winnings</span>
      </a>

      <!-- Results -->
      <a href="{{route('agent.results')}}" class="group flex-1 min-w-[70px] py-3 px-4 text-sm font-semibold bg-white bg-gray-800 shadow rounded-lg p-3 dark:bg-gray-800 rounded-xl 
                    transition duration-150 ease-in-out hover:border-blue-500 active:border-blue-700 focus:outline-none 
                    focus:ring focus:ring-blue-200 flex flex-col items-center justify-center">
        <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"
            class="lucide lucide-shapes group-hover:text-blue-600 text-gray-600 dark:text-white mb-1 w-12 h-12">
          <path d="M8.3 10a.7.7 0 0 1-.626-1.079L11.4 3a.7.7 0 0 1 1.198-.043L16.3 8.9a.7.7 0 0 1-.572 1.1Z"/>
          <rect x="3" y="14" width="7" height="7" rx="1"/>
          <circle cx="17.5" cy="17.5" r="3.5"/>
        </svg>
        <span class="text-xs font-medium mt-1 text-gray-500 dark:text-gray-400 group-hover:text-blue-600">Results</span>
      </a>

      <!-- Reports -->
      <a href="{{route('agent.reports')}}" class="group flex-1 min-w-[70px] py-3 px-4 text-sm font-semibold bg-white bg-gray-800 shadow rounded-lg p-3 dark:bg-gray-800 rounded-xl 
                    transition duration-150 ease-in-out hover:border-blue-500 active:border-blue-700 focus:outline-none 
                    focus:ring focus:ring-blue-200 flex flex-col items-center justify-center">
        <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"
            class="lucide lucide-files group-hover:text-blue-600 text-gray-600 dark:text-white mb-1 w-12 h-12">
          <path d="M20 7h-3a2 2 0 0 1-2-2V2"/>
          <path d="M9 18a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h7l4 4v10a2 2 0 0 1-2 2Z"/>
          <path d="M3 7.6v12.8A1.6 1.6 0 0 0 4.6 22h9.8"/>
        </svg>
        <span class="text-xs font-medium mt-1 text-gray-500 dark:text-gray-400 group-hover:text-blue-600">Reports</span>
      </a>

    </div>

    <div class="mt-4">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Today's Top Combinations</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($topCombinations as $gameType => $combinations)
                <div class="bg-white dark:bg-gray-800 shadow rounded-xl p-4">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 capitalize mb-2">{{ $gameType }}</h3>
                    <ul class="space-y-2">
                        @forelse($combinations as $combination)
                            <li class="flex justify-between items-center bg-gray-100 dark:bg-gray-700 p-2 rounded-md">
                                <span class="text-gray-900 dark:text-white font-medium">{{ $combination->bet_number }}</span>
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ $combination->total }} bets</span>
                            </li>
                        @empty
                            <li class="text-sm text-gray-500 dark:text-gray-400">No bets yet for this game type.</li>
                        @endforelse
                    </ul>
                </div>
            @endforeach
        </div>
    </div>
    <!-- Section 4 Visual Cutoff Alert -->
    <div
        x-data="{ showCutoff: false }"
        x-show="showCutoff"
        x-transition.opacity
        x-cloak
        class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50"
    >
        <div class="bg-white rounded-lg shadow-lg w-full p-6 text-center">
            <h2 class="text-2xl font-bold text-red-600 mb-2">🚨 Cutoff Reached!</h2>
            <p class="text-gray-700">Betting is no longer allowed for the current draw time.</p>
            <button
                @click="showCutoff = false"
                class="mt-4 bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition"
            >
                Close
            </button>
        </div>
    </div>
      <!-- Cutoff Alerts -->
      <div 
          x-data="{
              show14: false,
              show17: false,
              show21: false
          }" 
          x-init="setInterval(() => {
              const now = new Date();
              const h = now.getHours();
              const m = now.getMinutes();

              if (h === 13 && m === 50) show14 = true;
              if (h === 16 && m === 50) show17 = true;
              if (h === 20 && m === 50) show21 = true;
          }, 10000)"
          class="z-50"
          x-cloak
      >
          <!-- 2PM Draw Alert -->
          <div x-show="show14" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center">
              <div class="bg-white rounded-lg shadow-lg  w-full p-6 text-center border-l-8 border-red-600">
                  <h2 class="text-xl font-bold text-red-600 mb-2">🚨 2PM Cutoff Reached</h2>
                  <p class="text-gray-700">Betting for the 2PM draw is now closed.</p>
                  <button @click="show14 = false" class="mt-4 bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                      Close
                  </button>
              </div>
          </div>

          <!-- 5PM Draw Alert -->
          <div x-show="show17" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center">
              <div class="bg-white rounded-lg shadow-lg  w-full p-6 text-center border-l-8 border-yellow-500">
                  <h2 class="text-xl font-bold text-yellow-600 mb-2">🚨 5PM Cutoff Reached</h2>
                  <p class="text-gray-700">Betting for the 5PM draw is now closed.</p>
                  <button @click="show17 = false" class="mt-4 bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">
                      Close
                  </button>
              </div>
          </div>

          <!-- 9PM Draw Alert -->
          <div x-show="show21" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center">
              <div class="bg-white rounded-lg shadow-lg  w-full p-6 text-center border-l-8 border-indigo-600">
                  <h2 class="text-xl font-bold text-indigo-600 mb-2">🚨 9PM Cutoff Reached</h2>
                  <p class="text-gray-700">Betting for the 9PM draw is now closed.</p>
                  <button @click="show21 = false" class="mt-4 bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                      Close
                  </button>
              </div>
          </div>
      </div>
    </div>
  </div>
</x-layouts.panel>
