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
  let submittedStubIds = []; // 🔽 Tracks all submitted stub_ids for this session

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

function updateAvailableGamesForToday() {
  const now = new Date();
  const hour = now.getHours();
  const minute = now.getMinutes();

  let targetDate = new Date(now);
  let disableDrawButtons = false;

  // ✅ Between 8:50 PM and 9:00 PM → disable betting
  if (hour === 20 && minute >= 50 || hour === 21 && minute === 0) {
    disableDrawButtons = true;
  }

  // ✅ After 9:01 PM → switch to tomorrow's schedule
  const isAfter9PM = hour > 21 || (hour === 21 && minute >= 1);
  if (isAfter9PM) {
    targetDate.setDate(now.getDate() + 1);
  }

  const currentDay = days[targetDate.getDay()];
  const allowedGames = gameScheduleByDay[currentDay] || [];

  const container = document.querySelector('#step-1 > div.grid') || document.querySelector('#game-type-container');
  gameTypeInputs.forEach(btn => {
    btn.style.display = allowedGames.includes(btn.dataset.value) ? 'inline-block' : 'none';
  });

  const visibleButtons = Array.from(gameTypeInputs).filter(btn => btn.style.display !== 'none');
  if (visibleButtons.length === 2) {
    container?.classList.remove('grid-cols-3');
    container?.classList.add('flex', 'justify-center', 'gap-4');
  } else {
    container?.classList.remove('flex', 'justify-center', 'gap-4');
    container?.classList.add('grid', 'grid-cols-3', 'gap-4');
  }

  // ✅ Enable or disable draw buttons based on cutoff
  drawTimeInputs.forEach(drawBtn => {
    if (disableDrawButtons) {
      drawBtn.disabled = true;
      drawBtn.classList.add('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
    } else {
      drawBtn.disabled = false;
      drawBtn.classList.remove('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
    }
  });

  // ✅ Add warning after 9:01 PM
  const warningId = 'cutoff-warning';
  let warning = document.getElementById(warningId);
  if (isAfter9PM) {
    if (!warning) {
      warning = document.createElement('div');
      warning.id = warningId;
      warning.className = 'text-yellow-600 dark:text-yellow-300 text-center mt-2 text-sm font-medium';
      warning.textContent = '⚠️ Bets placed now are for tomorrow’s draw.';
      document.getElementById('modal-title')?.after(warning);
    }
  } else {
    warning?.remove();
  }
}




  function generateStubID(gameType, drawTime) {
    const now = new Date();
    const ymd = now.toISOString().slice(2, 10).replace(/-/g, '');
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

  const now = new Date();

  // 🔽 CUTOFF LOGIC: START
  let gameDate;
  const currentHour = now.getHours();
  const currentMinute = now.getMinutes();

  if (currentHour > 21 || (currentHour === 21 && currentMinute > 0)) {
    now.setDate(now.getDate() + 1); // add 1 day
  }
  gameDate = now.toISOString().split('T')[0];
  // 🔼 CUTOFF LOGIC: END

  numbers.forEach(number => {
    const stubId = generateStubID(gameType, drawTime);

    const bet = {
      stub_id: stubId,
      game_type: gameType,
      game_draw: drawTime,
      bet_number: number,
      amount: null,
      game_date: gameDate
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
  if (offlineBets.length === 0) return;

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
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        alert('Offline bets synced successfully.');
        localStorage.removeItem('offlineBets');
        checkOfflineBetsBadge();
      } else {
        console.warn('Server rejected offline sync response.');
      }
    })
    .catch(err => {
      console.error('Auto-sync error:', err);
    })
    .finally(() => {
      if (syncIndicator) syncIndicator.classList.add('hidden');
    });
}


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

        // 🔽 Collect stub_ids to use for printing all session bets
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
      updateAvailableGamesForToday();
          // ✅ Ensure draw time buttons are not disabled
          drawTimeInputs.forEach(btn => {
            btn.disabled = false;
            btn.classList.remove('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
          });
      betNumberInput.value = '';
      permutationList.innerHTML = '';
      summaryList.innerHTML = '';
      selectedPermutations = [];
      allBets = [];
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

// 🔽 MULTI-RECEIPT: Replace to accept stub ID array
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
