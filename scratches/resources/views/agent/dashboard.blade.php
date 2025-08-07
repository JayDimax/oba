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
    <!--<div class="flex items-center justify-between mb-0">-->
    <!--  <div class="flex space-x-2 justify-end items-center">-->
    <!--    <button id="sync-offline-bets" onclick="syncOfflineBets()" class="px-2 py-1 rounded-xl text-sm text-gray-700 dark:text-white hover:bg-gray-200 dark:hover:bg-gray-700 transition flex items-center gap-2">-->
    <!--      <svg id="sync-icon" class="svg-color dark:text-white" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">-->
    <!--        <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>-->
    <!--        <path d="M3 3v5h5"/>-->
    <!--        <path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/>-->
    <!--        <path d="M16 16h5v5"/>-->
    <!--      </svg>-->
    <!--      <span class="text-xs">Sync</span>-->
    <!--      <div id="sync-indicator" class="hidden flex items-center space-x-2 text-blue-600 dark:text-blue-300 text-sm mt-2">-->
    <!--        <svg class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">-->
    <!--          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>-->
    <!--          <path class="opacity-75" fill="currentColor"-->
    <!--            d="M4 12a8 8 0 018-8v4l5-5-5-5v4a12 12 0 00-12 12h4z">-->
    <!--          </path>-->
    <!--        </svg>-->
    <!--        <span>Syncing offline bets...</span>-->
    <!--      </div>-->

          <!-- Add this badge to your HTML (e.g., near the dashboard button) -->
    <!--      <span id="offline-badge" class="hidden text-xs bg-red-600 text-white px-2 py-1 rounded">Offline Bets Pending</span>-->
    <!--    </button>-->
    <!--  </div>-->
    <!--</div>-->

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
  </div>
</x-layouts.panel>
