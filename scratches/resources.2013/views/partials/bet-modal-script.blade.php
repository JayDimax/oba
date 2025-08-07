<script>
document.addEventListener('DOMContentLoaded', function () {
  const openModalBtn = document.getElementById('open-bet-modal');
  const betModal = document.getElementById('bet-modal');
  const steps = document.querySelectorAll('.step');
  const modalTitle = document.getElementById('modal-title');
  const gameTypeInputs = document.querySelectorAll('.game-type-btn');
  const drawTimeInputs = document.querySelectorAll('.draw-time-btn');
  const betNumberInput = document.getElementById('bet_number');
  const betNumberError = document.getElementById('bet-number-error');
  const permutationResults = document.getElementById('permutation-results');
  const permutationList = document.getElementById('permutation-list');
  const prevStepBtn = document.getElementById('prev-step');
  const nextStep3Btn = document.getElementById('next-step-3');
  const summaryList = document.getElementById('summary-list');
  const betAmountError = document.getElementById('bet-amount-error');
  const submitBetBtn = document.getElementById('submit-bet');
  const addAnotherBetBtn = document.getElementById('add-another-bet');
  const offlineBadge = document.getElementById('offline-badge'); // ✅
  const printReceiptBtn = document.createElement('button');
  printReceiptBtn.textContent = 'Print Receipt';
  printReceiptBtn.className = 'ml-2 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 hidden';
  addAnotherBetBtn.parentNode.insertBefore(printReceiptBtn, addAnotherBetBtn.nextSibling);
  const successAlert = document.getElementById('success-alert');
  const cancelModalBtn = document.getElementById('cancel-bet-modal');
  const hiddenGameType = document.getElementById('game_type');
  const hiddenGameDraw = document.getElementById('game_draw');
  let currentStep = 1;
  let selectedGameType = null;
  let selectedDrawTime = null;
  let selectedPermutations = [];
  let allBets = [];
  let submittedStubIds = []; // Tracks stub_ids for session printing
  const gameScheduleByDay = {
    "Monday":    ["L2", "S3", "4D"],
    "Tuesday":   ["L2", "S3"],
    "Wednesday": ["L2", "S3", "4D"],
    "Thursday":  ["L2", "S3"],
    "Friday":    ["L2", "S3", "4D"],
    "Saturday":  ["L2", "S3"],
    "Sunday":    ["L2", "S3"]
  };
  const days = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];

  // --- Helper function to parse draw time labels into hours ---
  // Assumes draw time labels are like "2PM", "5PM", "9PM"
  function parseDrawHour(drawTimeLabel) {
    const match = drawTimeLabel.match(/^(\d+)(AM|PM)$/i);
    if (!match) return -1; // Invalid format

    let hour = parseInt(match[1], 10);
    const period = match[2].toUpperCase();

    if (period === 'AM' && hour === 12) hour = 0;
    if (period === 'PM' && hour !== 12) hour += 12;

    return hour;
  }

function updateAvailableGamesForToday() {
  const now = new Date();
  const currentDay = now.toLocaleDateString('en-US', { weekday: 'long' });

  const gameDateInput = document.getElementById('game_date');
  const after915PM = now.getHours() === 21 && now.getMinutes() >= 15 || now.getHours() > 21;
  const today = new Date();
  const tomorrow = new Date(today);
  tomorrow.setDate(today.getDate() + 1);

  const finalGameDate = after915PM ? tomorrow : today;
  if (gameDateInput) {
    gameDateInput.value = finalGameDate.toISOString().slice(0, 10); // YYYY-MM-DD
  }

  // Determine selected game date’s day
  const gameDate = finalGameDate;
  const gameDayName = gameDate.toLocaleDateString('en-US', { weekday: 'long' });
  const allowedGames = gameScheduleByDay[gameDayName] || [];

  // === Show/Hide Game Type Buttons ===
  const gameTypeBtns = document.querySelectorAll('.game-type-btn');
  gameTypeBtns.forEach(btn => {
    const gameType = btn.getAttribute('data-value');
    if (allowedGames.includes(gameType)) {
      btn.classList.remove('hidden');
    } else {
      btn.classList.add('hidden');
    }
  });

  // === Draw Button Cutoff Handling ===
  const drawTimeBtns = document.querySelectorAll('.draw-time-btn');
  drawTimeBtns.forEach(btn => {
    const drawHour = parseInt(btn.getAttribute('data-value')); // 14, 17, 21
    const drawTime = new Date(now);
    drawTime.setHours(drawHour, 0, 0, 0);

    const cutoffTime = new Date(drawTime.getTime() - 10 * 60000); // 10 mins before

    const isToday = gameDate.toDateString() === today.toDateString();

    const isAfter915PM = now.getHours() > 21 || (now.getHours() === 21 && now.getMinutes() >= 15);

    // If it's today and before 9:15PM, apply cutoff
    if (isToday && !isAfter915PM) {
        if (isPastCutoff) {
            btn.disabled = true;
            btn.classList.add('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
        } else {
            // Apply warning if near cutoff
            btn.disabled = false;
            btn.classList.remove('opacity-50', 'cursor-not-allowed', 'pointer-events-none');

            const minutesToCutoff = Math.floor((cutoffTime - now) / 60000);
            if (minutesToCutoff <= 10) {
                btn.classList.add('border-yellow-500', 'text-yellow-600');
                btn.title = `Cutoff in ${minutesToCutoff} minute(s)!`;
            } else {
                btn.classList.remove('border-yellow-500', 'text-yellow-600');
                btn.removeAttribute('title');
            }
        }
    } else {
        // After 9:15PM or betting for tomorrow – all draws are enabled
        btn.disabled = false;
        btn.classList.remove('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
        btn.classList.remove('border-yellow-500', 'text-yellow-600');
        btn.removeAttribute('title');
    }
    
  });
}


    function getAdjustedGameDate() {
    const now = new Date();
    // Get today's 9:00 PM
    const cutoff = new Date(now);
    cutoff.setHours(21, 0, 0, 0); // 9:00 PM today
    // If it's after 9PM, mark it for tomorrow's draw
    if (now > cutoff) {
      now.setDate(now.getDate() + 1);
    }
    return now.toISOString().split('T')[0];
  }
  function generateStubID(gameType, drawTime) {
    const now = new Date();
    const y = String(now.getFullYear()).slice(2);         // "25"
    const m = String(now.getMonth() + 1).padStart(2, '0'); // "07"
    const d = String(now.getDate()).padStart(2, '0');      // "30"
    const ymd = `${y}${m}${d}`;                           
    const rand = Math.floor(Math.random() * 10000).toString().padStart(4, '0');
    return `${ymd}-${gameType}${drawTime}-${rand}`;
  }
  function goToStep(step) {
    currentStep = step;
    steps.forEach((el, idx) => el.classList.toggle('hidden', idx !== step - 1));
    const titles = ['Select Game Type', 'Select Draw Time', 'Enter Bet Number', 'Enter Amount'];
    modalTitle.textContent = titles[step - 1];
  }
  function validateBetNumber(number, type) {
    const valid = /^\d+$/;
    const len = { 'L2': 2, 'S3': 3, '4D': 4 }[type];
    return valid.test(number) && number.length === len;
  }
  function generatePermutations(str) {
    const result = new Set();
    function permute(arr, m = []) {
      if (arr.length === 0) result.add(m.join(''));
      else {
        for (let i = 0; i < arr.length; i++) {
          let curr = arr.slice();
          let next = curr.splice(i, 1);
          permute(curr, m.concat(next));
        }
      }
    }
    permute(str.split(''));
    return Array.from(result);
  }
  function renderPermutations(perms, original) {
    permutationList.innerHTML = '';
    selectedPermutations = [original];
    perms.forEach(perm => {
      if (perm === original) return;
      const div = document.createElement('div');
      div.className = 'flex items-center space-x-2';
      const checkbox = document.createElement('input');
      checkbox.type = 'checkbox';
      checkbox.id = `perm-${perm}`;
      checkbox.value = perm;
      checkbox.classList.add('perm-checkbox');
      const label = document.createElement('label');
      label.setAttribute('for', `perm-${perm}`);
      label.textContent = perm;
      label.className = 'text-gray-800 dark:text-white';
      checkbox.addEventListener('change', function () {
        if (this.checked) selectedPermutations.push(this.value);
        else selectedPermutations = selectedPermutations.filter(p => p !== this.value);
      });
      div.appendChild(checkbox);
      div.appendChild(label);
      permutationList.appendChild(div);
    });
  }
function renderSummary(numbers, gameType, drawTime) {
  summaryList.innerHTML = '';
  allBets = [];
  const adjustedDate = getAdjustedGameDate(); // ✅ Use the properly calculated date
  numbers.forEach(number => {
    const stubId = generateStubID(gameType, drawTime);
    const bet = {
      stub_id: stubId,
      game_type: gameType,
      game_draw: drawTime,
      bet_number: number,
      amount: null,
      game_date: adjustedDate  // ✅ always trust this date
    };
    allBets.push(bet);
    const div = document.createElement('div');
    div.className = 'p-3 bg-gray-100 dark:bg-gray-700 rounded flex justify-between items-center';
    div.innerHTML = `
      <div>
        <div class="text-sm font-medium text-gray-800 dark:text-white">${gameType} • ${drawTime}</div>
        <div class="font-semibold text-gray-900 dark:text-white">Number: ${number}</div>
        <div class="text-xs text-gray-500">Stub ID: ${stubId}</div>
      </div>
      <div>
        <input type="number" min="0" class="w-24 py-3 px-2 text-2xl text-center border border-gray-300 rounded dark:bg-gray-600 dark:text-white amount-input" />
      </div>
    `;
    summaryList.appendChild(div);
  });
}
  function checkOfflineBetsBadge() {
    const offlineBets = JSON.parse(localStorage.getItem('offlineBets') || '[]');
    if (offlineBets.length > 0) offlineBadge?.classList.remove('hidden');
    else offlineBadge?.classList.add('hidden');
  }
  function saveBetsOffline(bets) {
    const offlineBets = JSON.parse(localStorage.getItem('offlineBets') || '[]');
    offlineBets.push(...bets);
    localStorage.setItem('offlineBets', JSON.stringify(offlineBets));
    checkOfflineBetsBadge();
    alert('Offline: Bets saved locally. Will sync when connected.');
  }
  
  function syncOfflineBets() {
  const offlineBets = JSON.parse(localStorage.getItem('offlineBets') || '[]');
  console.log('Attempting to sync offline bets:', offlineBets);
  
  if (offlineBets.length === 0) {
    console.log('No offline bets to sync.');
    return;
  }
  
  const syncIndicator = document.getElementById('sync-indicator');
  if (syncIndicator) syncIndicator.classList.remove('hidden');
  
  fetch('/agent/bets/store', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    },
    body: JSON.stringify({ bets: offlineBets })
  })
  .then(res => {
    console.log('Sync response status:', res.status);
    if (!res.ok) {
      throw new Error(`HTTP error! status: ${res.status}`);
    }
    return res.json();
  })
  .then(data => {
    console.log('Sync response data:', data);
    if (data.success) {
      alert('Offline bets synced successfully.');
      localStorage.removeItem('offlineBets');
      checkOfflineBetsBadge();
    } else {
      alert('Sync failed: ' + (data.message || 'Unknown server error'));
      console.warn('Server rejected offline sync response:', data);
    }
  })
  .catch(err => {
    console.error('Auto-sync error:', err);
    alert(`Auto-sync error: ${err.message}. Please check your connection or try again later.`);
  })
  .finally(() => {
    if (syncIndicator) syncIndicator.classList.add('hidden');
  });
}

// Attach to online event and interval as before
window.addEventListener('online', () => {
  console.log('Browser online event fired');
  syncOfflineBets();
});

  window.addEventListener('online', syncOfflineBets);
  checkOfflineBetsBadge();
    // ✅ Auto-sync every 30 seconds while online
  setInterval(() => {
    if (navigator.onLine) {
      syncOfflineBets();
    }
  }, 30000); // 30000 ms = 30 seconds
  if (submitBetBtn) {
    submitBetBtn.addEventListener('click', () => {
      betAmountError.classList.add('hidden');
      const amountInputs = summaryList.querySelectorAll('input.amount-input');
      let allValid = true;
      amountInputs.forEach((input, idx) => {
        const val = parseFloat(input.value);
        if (!isNaN(val) && val > 0) {
          allBets[idx].amount = val;
        } else {
          allValid = false;
        }
      });
      if (!allValid) {
        betAmountError.textContent = 'Please enter a valid amount for each bet.';
        betAmountError.classList.remove('hidden');
        return;
      }
      fetch('/agent/bets/store', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: JSON.stringify({ bets: allBets })
      })
      .then(res => {
        if (!res.ok) throw new Error('Failed');
        return res.json();
      })
      .then(data => {
        if (!data.success) return alert('Your account is deactivated.');
        // Collect stub_ids
        allBets.forEach(b => {
          if (!submittedStubIds.includes(b.stub_id)) {
            submittedStubIds.push(b.stub_id);
          }
        });
        successAlert.classList.remove('hidden');
        submitBetBtn.classList.add('hidden');
        addAnotherBetBtn.classList.remove('hidden');
        printReceiptBtn.classList.remove('hidden');
        printReceiptBtn.scrollIntoView({ behavior: 'smooth', block: 'center' });
        const printHint = document.getElementById('print-hint') || document.createElement('div');
        printHint.id = 'print-hint';
        printHint.className = 'mt-2 text-sm text-green-600 dark:text-green-400';
        printHint.textContent = 'Bet saved! You may now print the receipt.';
        summaryList.parentNode.insertBefore(printHint, summaryList.nextSibling);
        summaryList.innerHTML = '';
        checkOfflineBetsBadge();
      })
      .catch(err => {
        console.warn('Saving offline instead...');
        saveBetsOffline(allBets);
        summaryList.innerHTML = '';
      });
    });
  }
  if (openModalBtn) {
    openModalBtn.addEventListener('click', () => {
      betModal.classList.remove('hidden');
      goToStep(1);
      updateAvailableGamesForToday(); // Ensure correct state on open
      betNumberInput.value = '';
      permutationList.innerHTML = '';
      summaryList.innerHTML = '';
      selectedPermutations = [];
      allBets = [];
      submittedStubIds = []; // Reset session stub IDs
      submitBetBtn.classList.remove('hidden');
      addAnotherBetBtn.classList.add('hidden');
      printReceiptBtn.classList.add('hidden');
      successAlert.classList.add('hidden');
    });
  }
  if (cancelModalBtn) {
    cancelModalBtn.addEventListener('click', () => {
      betModal.classList.add('hidden');
      refreshDashboard();
    });
  }
  function refreshDashboard() {
    fetch('/agent/dashboard/gross-total')
      .then(res => res.json())
      .then(data => {
        const grossElement = document.getElementById('dashboard-gross');
        if (grossElement) {
          grossElement.textContent = `₱${parseFloat(data.total_gross).toFixed(2)}`;
        }
      })
      .catch(err => console.error('Dashboard update error:', err));
  }
  if (addAnotherBetBtn) {
    addAnotherBetBtn.addEventListener('click', () => {
      betNumberInput.value = '';
      permutationList.innerHTML = '';
      summaryList.innerHTML = '';
      selectedPermutations = [];
      allBets = [];
      // Do NOT reset submittedStubIds here, as user might want to print multiple batches
      submitBetBtn.classList.remove('hidden');
      addAnotherBetBtn.classList.add('hidden');
      printReceiptBtn.classList.add('hidden');
      successAlert.classList.add('hidden');
      goToStep(1);
      updateAvailableGamesForToday();
    });
  }
printReceiptBtn.addEventListener('click', () => {
  if (submittedStubIds.length > 0) {
    openBladeReceipt(submittedStubIds);
  }
});
// MULTI-RECEIPT: Updated to accept stub ID array
function openBladeReceipt(stubIds) {
  const url = "{{ route('agent.receipts.multi', 'stub_id_placeholder') }}"
                .replace('stub_id_placeholder', encodeURIComponent(stubIds.join(',')));
  window.open(url, '_blank');
}
  gameTypeInputs.forEach(btn => {
    btn.addEventListener('click', () => {
      selectedGameType = btn.dataset.value;
      hiddenGameType.value = selectedGameType;
      goToStep(2);
    });
  });
  drawTimeInputs.forEach(btn => {
    btn.addEventListener('click', () => {
      selectedDrawTime = btn.dataset.value;
      hiddenGameDraw.value = selectedDrawTime;
      goToStep(3);
    });
  });
  if (betNumberInput) {
    betNumberInput.addEventListener('input', () => {
      const number = betNumberInput.value.trim();
      if (!selectedGameType) return;
      if (validateBetNumber(number, selectedGameType)) {
        betNumberError.classList.add('hidden');
        if (selectedGameType === 'L2') {
          selectedPermutations = [number];
          permutationResults.classList.add('hidden');
        } else {
          const perms = generatePermutations(number);
          renderPermutations(perms, number);
          permutationResults.classList.remove('hidden');
        }
      } else {
        betNumberError.textContent = `Invalid input for ${selectedGameType}.`;
        betNumberError.classList.remove('hidden');
        permutationResults.classList.add('hidden');
      }
    });
  }
  if (nextStep3Btn) {
    nextStep3Btn.addEventListener('click', () => {
      if (selectedPermutations.length === 0) {
        alert('Please select at least one combination.');
        return;
      }
      renderSummary(selectedPermutations, selectedGameType, selectedDrawTime);
      goToStep(4);
    });
  }
  if (prevStepBtn) {
    prevStepBtn.addEventListener('click', () => {
      if (currentStep > 1) goToStep(currentStep - 1);
    });
  }
});
</script>