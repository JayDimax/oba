


<!-- Betting Modal -->
<div id="bet-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
  <div class="bg-white dark:bg-gray-800 p-6 rounded-xl w-full max-w-2xl mx-4 relative">

        <div id="live-preview" class="mb-4 hidden">
          <h3 class="text-sm font-semibold text-white mb-2 text-center">BETS</h3>
          <ul id="live-preview-list" class="list-none p-0 m-0">
            
          </ul>
        </div>
    <!-- ✅ Insert the live preview section here -->
    <div id="summary-list">...</div>




    <!-- Close Button -->
    <button id="cancel-bet-modal" type="button" class="absolute top-4 right-4 text-gray-500 dark:text-gray-300 hover:text-black dark:hover:text-white text-xl font-bold" aria-label="Close">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
    </button>



    <!-- Success Alert -->
    <div id="success-alert" class="p-3 rounded mb-4 text-base text-center font-semibold bg-green-100 text-green-800 dark:bg-gray-700 dark:text-white dark:border-gray-600 hidden">
      Bet Placed Successfully!
    </div>



    <h2 id="modal-title" class="text-xl font-bold mb-4 text-gray-800 dark:text-white">Select Game Type</h2>

    <form id="bet-form">
      <!-- Step 1: Game Type -->
      <div class="step" id="step-1">
        <div id="game-type-container" class="flex flex-wrap justify-center gap-4">
          <button type="button" class="game-type-btn py-3 px-4 text-lg font-semibold border border-gray-400 text-gray-800 dark:text-white rounded-xl hover:border-blue-500 hover:text-blue-600" data-value="L2">Last Two L2</button>
          <button type="button" class="game-type-btn py-3 px-4 text-lg font-semibold border border-gray-400 text-gray-800 dark:text-white rounded-xl hover:border-blue-500 hover:text-blue-600" data-value="S3">Swetres S3</button>
          <button type="button" class="game-type-btn py-3 px-4 text-lg font-semibold border border-gray-400 text-gray-800 dark:text-white rounded-xl hover:border-blue-500 hover:text-blue-600" data-value="4D">4 Digits 4D</button>
        </div>
      </div>

      <!-- Step 2: Draw Time -->
      <div class="step hidden" id="step-2">
        <div class="grid grid-cols-3 gap-4 mt-2">
          <button type="button" class="draw-time-btn py-3 px-4 text-lg font-semibold border border-gray-400 text-gray-800 dark:text-white rounded-xl hover:border-blue-500 hover:text-blue-600" data-value="14">2:00 PM</button>
          <button type="button" class="draw-time-btn py-3 px-4 text-lg font-semibold border border-gray-400 text-gray-800 dark:text-white rounded-xl hover:border-blue-500 hover:text-blue-600" data-value="17">5:00 PM</button>
          <button type="button" class="draw-time-btn py-3 px-4 text-lg font-semibold border border-gray-400 text-gray-800 dark:text-white rounded-xl hover:border-blue-500 hover:text-blue-600" data-value="21">9:00 PM</button>
        </div>
      </div>

      <!-- Step 3: Bet Number Entry -->
      <div class="step hidden" id="step-3">
        <input id="bet_number" type="text" class="w-full py-4 px-3 text-2xl text-center font-semibold tracking-widest border border-gray-400 rounded-xl mb-4 dark:bg-gray-700 dark:text-white dark:border-gray-600" />
        <p id="bet-number-error" class="text-red-600 text-sm hidden"></p>
        <div id="permutation-results" class="hidden mt-4">
          <p class="font-medium text-gray-700 dark:text-gray-300">Select Combinations:</p>
          <div id="permutation-list" class="grid grid-cols-4 gap-2 mt-2"></div>
        </div>
        <div class="flex justify-between mt-6">
          <button type="button" id="prev-step" class="py-2 px-4 text-base font-semibold bg-gray-200 dark:bg-gray-600 text-gray-800 dark:text-white rounded-xl hover:bg-gray-300">Back</button>
          <button type="button" id="next-step-3" class="py-2 px-4 text-base font-semibold bg-blue-600 text-white rounded-xl hover:bg-blue-700">Next</button>
        </div>
      </div>

      <!-- Step 4: Summary and Amount -->
      <div class="step hidden" id="step-4">
        <div id="summary-lists" class="space-y-4 mb-3 text-gray-800 dark:text-white"></div>
        <p id="bet-amount-error" class="text-red-600 text-sm hidden"></p>
        <div class="mt-4 space-y-3">
          <button id="submit-bet" type="submit" class="py-3 px-4 text-lg font-semibold bg-green-600 text-white rounded-xl hover:bg-green-700 w-full">Place Bet</button>
          <button type="button" id="add-another-bet" class="py-3 px-4 text-lg font-semibold bg-green-600 text-white rounded-xl hover:bg-green-700 w-full hidden">Bet Again</button>
        </div>
      </div>

      <!-- Hidden Fields -->
      <input type="hidden" id="game_type" name="game_type">
      <input type="hidden" id="game_draw" name="game_draw">
    </form>
  </div>
</div>

<!-- Open Modal Button -->


<!-- Dashboard Gross Display -->
<div id="dashboard-gross" class="mt-4 text-lg font-bold"></div><?php /**PATH D:\laragon\www\oba\resources\views/partials/bet-modal.blade.php ENDPATH**/ ?>