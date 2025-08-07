<script>
// === BET MODAL SCRIPT (UPDATED FOR MINIMALIST UI AND FULL PERM SUPPORT) ===
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
  const printBetBtn = document.getElementById('print-bet');
  const successAlert = document.getElementById('success-alert');
  const cancelModalBtn = document.getElementById('cancel-bet-modal');

  const hiddenGameType = document.getElementById('game_type');
  const hiddenGameDraw = document.getElementById('game_draw');

  let currentStep = 1;
  let selectedGameType = null;
  let selectedDrawTime = null;
  let selectedPermutations = [];
  let allBets = [];

  // Map day name to allowed games
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
    const result = [];
    function permute(arr, m = []) {
      if (arr.length === 0) result.push(m.join(''));
      else {
        for (let i = 0; i < arr.length; i++) {
          let curr = arr.slice();
          let next = curr.splice(i, 1);
          permute(curr, m.concat(next));
        }
      }
    }
    permute(str.split(''));
    return [...new Set(result)];
  }

  function renderPermutations(perms) {
    permutationList.innerHTML = '';
    selectedPermutations = [];
    perms.forEach(perm => {
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
        if (this.checked) {
          if (!selectedPermutations.includes(this.value)) {
            selectedPermutations.push(this.value);
          }
        } else {
          selectedPermutations = selectedPermutations.filter(p => p !== this.value);
        }
      });

      div.appendChild(checkbox);
      div.appendChild(label);
      permutationList.appendChild(div);
    });
  }

  function renderSummary(numbers, gameType, drawTime) {
    summaryList.innerHTML = '';
    allBets = [];

    numbers.forEach(number => {
      const stubId = generateStubID();
      allBets.push({
        stub_id: stubId,
        game_type: gameType,
        game_draw: drawTime,
        bet_number: number,
        amount: null
      });

      const div = document.createElement('div');
      div.className = 'p-3 bg-gray-100 dark:bg-gray-700 rounded flex justify-between items-center';

      div.innerHTML = `
        <div>
          <div class="text-sm font-medium text-gray-800 dark:text-white">${gameType} • ${formatDraw(drawTime)}</div>
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

  function generateStubID() {
    const now = new Date();
    return now.toISOString().slice(2, 10).replace(/-/g, '') + '-' + Math.floor(Math.random() * 9999).toString().padStart(4, '0');
  }

  function formatDraw(code) {
    return { '14': '2PM', '17': '5PM', '21': '9PM' }[code] || code;
  }

  // Update game types visible based on current day only (ruling)
  function updateAvailableGamesForToday() {
    const now = new Date();
    const currentDay = days[now.getDay()];
    const allowedGames = gameScheduleByDay[currentDay] || [];

    const container = document.querySelector('#step-1 > div.grid');

    gameTypeInputs.forEach(btn => {
      if (allowedGames.includes(btn.dataset.value)) {
        btn.style.display = 'inline-block';
      } else {
        btn.style.display = 'none';
      }
    });

    // Center if only two games visible
    const visibleButtons = Array.from(gameTypeInputs).filter(btn => btn.style.display !== 'none');
    if (visibleButtons.length === 2) {
      container.classList.remove('grid-cols-3');
      container.classList.add('flex', 'justify-center', 'gap-4');
    } else {
      container.classList.remove('flex', 'justify-center', 'gap-4');
      container.classList.add('grid', 'grid-cols-3', 'gap-4');
    }
  }

  function resetModal() {
    goToStep(1);
    selectedGameType = null;
    selectedDrawTime = null;
    selectedPermutations = [];
    allBets = [];
    betNumberInput.value = '';
    betNumberError.classList.add('hidden');
    permutationResults.classList.add('hidden');
    permutationList.innerHTML = '';
    summaryList.innerHTML = '';
    betAmountError.classList.add('hidden');
    submitBetBtn.disabled = false;
    submitBetBtn.classList.remove('hidden');
    addAnotherBetBtn.classList.add('hidden');
    printBetBtn.classList.add('hidden');
    successAlert.classList.add('hidden');
    hiddenGameType.value = '';
    hiddenGameDraw.value = '';

    // Update game types on modal open by current day only
    updateAvailableGamesForToday();
  }

  if (openModalBtn && betModal) {
    openModalBtn.addEventListener('click', () => {
      betModal.classList.remove('hidden');
      resetModal();
    });
  }

  gameTypeInputs.forEach(btn => {
    btn.addEventListener('click', () => {
      selectedGameType = btn.dataset.value;
      hiddenGameType.value = selectedGameType;
      goToStep(2);
    });
  });

  // Draw time buttons always visible, no filtering on draw time
  drawTimeInputs.forEach(btn => {
    btn.addEventListener('click', () => {
      selectedDrawTime = btn.dataset.value;
      hiddenGameDraw.value = selectedDrawTime;
      goToStep(3);
    });
  });

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
        renderPermutations([number, ...perms]);
        permutationResults.classList.remove('hidden');
      }
    } else {
      betNumberError.textContent = `Invalid input for ${selectedGameType}.`;
      betNumberError.classList.remove('hidden');
      permutationResults.classList.add('hidden');
    }
  });

  nextStep3Btn.addEventListener('click', () => {
    if (selectedPermutations.length === 0) {
      alert('Please select at least one combination.');
      return;
    }
    renderSummary(selectedPermutations, selectedGameType, selectedDrawTime);
    goToStep(4);
  });

  prevStepBtn.addEventListener('click', () => {
    goToStep(2);
  });

  document.getElementById('bet-form').addEventListener('submit', function (e) {
    e.preventDefault();
    betAmountError.classList.add('hidden');
    const amountInputs = summaryList.querySelectorAll('input.amount-input');
    let valid = true;

    amountInputs.forEach((input, idx) => {
      const val = parseFloat(input.value);
      if (isNaN(val) || val <= 0) valid = false;
      else allBets[idx].amount = val;
    });

    if (!valid) {
      betAmountError.textContent = 'Please enter a valid amount for each bet.';
      betAmountError.classList.remove('hidden');
      return;
    }

    submitBetBtn.disabled = true;

    fetch('/bets/store', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      },
      body: JSON.stringify({ bets: allBets }),
    })
      .then(res => {
        if (!res.ok) throw new Error('Failed to save bets.');
        return res.json();
      })
      .then(data => {
        if (data.success) {
          successAlert.classList.remove('hidden');
          submitBetBtn.classList.add('hidden');
          addAnotherBetBtn.classList.remove('hidden');
          printBetBtn.classList.remove('hidden');
          printBetBtn.dataset.stubId = data.bets[0]?.stub_id || '';

          summaryList.querySelectorAll('input.amount-input').forEach(input => {
            input.disabled = true;
            input.classList.add('text-gray-700');
          });
        } else {
          alert('Server rejected the bets.');
          submitBetBtn.disabled = false;
        }
      })
      .catch(err => {
        console.error(err);
        alert('Something went wrong.');
        submitBetBtn.disabled = false;
      });
  });

  addAnotherBetBtn.addEventListener('click', () => {
    resetModal();
  });

  printBetBtn.addEventListener('click', () => {
    const stubId = printBetBtn.dataset.stubId;
    if (!stubId) return alert('No receipt available.');
    window.open(`/agent/receipts/${stubId}`, '_blank');
  });

  cancelModalBtn.addEventListener('click', () => {
    betModal.classList.add('hidden');
  });
});


</script>
