<x-app-layout>
  <div class="min-h-screen bg-gray-100 dark:bg-gray-900 p-4 flex flex-col space-y-6">

    <!-- SECTION 1: Header -->
    <div class="flex items-center justify-between mb-0">
        <!-- Profile Icon Only (top-right corner) -->
        <a href="{{ route('profile.edit') }}" class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition dark:text-white">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-round-icon lucide-user-round"><circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 0 0-16 0" /></svg>
        </a>
        <!-- Dark Mode Toggle and Sync Button -->
        <div class="flex space-x-2 justify-end items-center">
          <button id="darkModeToggle" class="ml-4 px-2 py-0 rounded-xl text-sm text-gray-700 dark:text-white hover:bg-gray-200 dark:hover:bg-gray-700 transition">
            🌙
          </button>
          <button id="sync-offline-bets" class="ml-4 px-2 py-0 rounded-xl text-sm text-gray-700 dark:text-white hover:bg-gray-200 dark:hover:bg-gray-700 transition flex items-center gap-2">
            <svg id="sync-icon" class="svg-color dark:text-white" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-refresh-ccw-icon lucide-refresh-ccw">
              <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
              <path d="M3 3v5h5"/>
              <path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/>
              <path d="M16 16h5v5"/>
            </svg>
            <!-- <span>Sync</span> -->
          </button>
        </div>
      </div>
        <div class="flex items-center justify-between mb-4">
          <div>
            <h1 class="text-4xl font-extrabold text-gray-900 dark:text-white" style="font-size: 2rem;">ORCAS</h1>
            <p class="text-xs text-gray-600 dark:text-gray-300 mb-6">Betting App</p>
          </div>

          <!-- Insert this just before or after your logo -->
          <img src="{{ asset('images/orca-logo.png') }}" alt="ORCAS Logo" class="h-16 w-auto mb-4">
        </div>

        
        <!-- SECTION 2: Today Gross & Add Bet -->
      <div 
        class="bg-white dark:bg-gray-800 w-full max-w-md mx-auto rounded-xl p-4  flex justify-between items-center"
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
    <div class="flex flex-wrap justify-between gap-2 sm:gap-4 mt-4 w-full max-w-md mx-auto">
      <!-- History -->
    <button class="flex-1 min-w-[70px] py-3 px-4 text-sm font-semibold text-gray-800 dark:text-white bg-white dark:bg-gray-800 rounded-xl 
                  transition duration-150 ease-in-out hover:border-blue-500 hover:text-blue-600 active:border-blue-700 active:text-blue-700 
                  focus:outline-none focus:ring focus:ring-blue-200 flex flex-col items-center justify-center">
      <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-history-icon lucide-history">
        <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
        <path d="M3 3v5h5"/>
        <path d="M12 7v5l4 2"/>
      </svg>
      
      <span class="text-xs text-gray-500 dark:text-gray-400 font-medium mt-1">History</span>
    </button>

    <!-- Winnings -->
    <button class="flex-1 min-w-[70px] py-3 px-4 text-sm font-semibold text-gray-800 dark:text-white bg-white dark:bg-gray-800 rounded-xl
                  transition duration-150 ease-in-out hover:border-blue-500 hover:text-blue-600 active:border-blue-700 active:text-blue-700
                  focus:outline-none focus:ring focus:ring-blue-200 flex flex-col items-center justify-center">
      <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-sparkle-icon lucide-sparkle mb-1 w-12 h-12 text-gray-600 dark:text-white">
        <path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"/>
      </svg>
      <span class="text-xs text-gray-500 dark:text-gray-400 font-medium mt-1">Winnings</span>
    </button>

    <!-- Results -->
    <button class="flex-1 min-w-[70px] py-3 px-4 text-sm font-semibold text-gray-800 dark:text-white bg-white dark:bg-gray-800 rounded-xl
                  transition duration-150 ease-in-out hover:border-blue-500 hover:text-blue-600 active:border-blue-700 active:text-blue-700
                  focus:outline-none focus:ring focus:ring-blue-200 flex flex-col items-center justify-center">
      <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shapes-icon lucide-shapes mb-1 w-12 h-12 text-gray-600 dark:text-white">
        <path d="M8.3 10a.7.7 0 0 1-.626-1.079L11.4 3a.7.7 0 0 1 1.198-.043L16.3 8.9a.7.7 0 0 1-.572 1.1Z"/>
        <rect x="3" y="14" width="7" height="7" rx="1"/>
        <circle cx="17.5" cy="17.5" r="3.5"/>
      </svg>
      <span class="text-xs text-gray-500 dark:text-gray-400 font-medium mt-1">Results</span>
    </button>

    <!-- Reports -->
    <button class="flex-1 min-w-[70px] py-3 px-4 text-sm font-semibold text-gray-800 dark:text-white bg-white dark:bg-gray-800 rounded-xl
                  transition duration-150 ease-in-out hover:border-blue-500 hover:text-blue-600 active:border-blue-700 active:text-blue-700
                  focus:outline-none focus:ring focus:ring-blue-200 flex flex-col items-center justify-center">
      <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-files-icon lucide-files mb-1 w-12 h-12 text-gray-600 dark:text-white">
        <path d="M20 7h-3a2 2 0 0 1-2-2V2"/>
        <path d="M9 18a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h7l4 4v10a2 2 0 0 1-2 2Z"/>
        <path d="M3 7.6v12.8A1.6 1.6 0 0 0 4.6 22h9.8"/>
      </svg>
      <span class="text-xs text-gray-500 dark:text-gray-400 font-medium mt-1">Reports</span>
    </button>
  </div>
</div>
  @include('partials.betting-enhanced')
  @include('partials.bet-modal')
  @include('partials.toggle')
  @include('partials.bet-modal-script')
  @include('partials.offlinebets')
</x-app-layout>
