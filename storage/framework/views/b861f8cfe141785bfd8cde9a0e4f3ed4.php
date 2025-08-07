<script>
  document.addEventListener('DOMContentLoaded', function () {
    // DOM Elements
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
    const successAlert = document.getElementById('success-alert');
    const cancelModalBtn = document.getElementById('cancel-bet-modal');
    const hiddenGameType = document.getElementById('game_type');
    const hiddenGameDraw = document.getElementById('game_draw');
    const livePreviewSection = document.getElementById('live-preview');
    const livePreviewList = document.getElementById('live-preview-list');

    // Create Print Receipt Button
    const printReceiptBtn = document.createElement('button');
    printReceiptBtn.className = 'ml-2 px-4 py-2 bg-blue-600 text-white text-center rounded hover:bg-blue-700 hidden flex items-center gap-2';
    printReceiptBtn.innerHTML = `
      <i data-lucide="printer"></i>
      <span>Print Receipt</span>
    `;
    // Append to DOM
    document.body.appendChild(printReceiptBtn);
    // Activate Lucide icons
    lucide.createIcons();
    addAnotherBetBtn.parentNode.insertBefore(printReceiptBtn, addAnotherBetBtn.nextSibling);

    // State
    let currentStep = 1;
    let selectedGameType = null;
    let selectedDrawTime = null;
    let selectedPermutations = [];
    let allBets = [];
    let submittedStubIds = [];

    // Game Schedule by Day
    const gameScheduleByDay = {
      "Monday": ["L2", "S3", "4D"],
      "Tuesday": ["L2", "S3"],
      "Wednesday": ["L2", "S3", "4D"],
      "Thursday": ["L2", "S3"],
      "Friday": ["L2", "S3", "4D"],
      "Saturday": ["L2", "S3"],
      "Sunday": ["L2", "S3"]
    };

    // Helpers
    function parseDrawHour(drawTimeLabel) {
      const match = drawTimeLabel.match(/^(\d+)(AM|PM)$/i);
      if (!match) return -1;
      let hour = parseInt(match[1], 10);
      const period = match[2].toUpperCase();
      if (period === 'AM' && hour === 12) hour = 0;
      if (period === 'PM' && hour !== 12) hour += 12;
      return hour;
    }

    function getAdjustedGameDate() {
      const now = new Date();
      const cutoff = new Date(now);
      cutoff.setHours(21, 0, 0, 0);
      if (now > cutoff) now.setDate(now.getDate() + 1);
      return now.toISOString().split('T')[0];
    }

    function generateStubID(gameType, drawTime) {
      const now = new Date();
      const y = String(now.getFullYear()).slice(2);
      const m = String(now.getMonth() + 1).padStart(2, '0');
      const d = String(now.getDate()).padStart(2, '0');
      const rand = Math.floor(Math.random() * 10000).toString().padStart(4, '0');
      return `${y}${m}${d}-${gameType}${drawTime}-${rand}`;
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

    async function isHotPickLocked(betNumber, gameType, drawTime, gameDate) {
      try {
        const response = await fetch(`/agent/check-hot-pick?bet_number=${encodeURIComponent(betNumber)}&game_type=${gameType}&game_draw=${drawTime}&game_date=${gameDate}`);
        const result = await response.json();
        return result.locked;
      } catch (error) {
        console.error('Error checking hot pick:', error);
        return false;
      }
    }
    //live update
    function updateLivePreview(bets) {
  const livePreviewSection = document.getElementById('live-preview');
  const livePreviewList = document.getElementById('live-preview-list');

  livePreviewSection.classList.remove('hidden');
  livePreviewList.innerHTML = '';

  const grouped = {};

  bets.forEach(bet => {
    const key = `${bet.game_draw} • ${bet.game_type}`;
    if (!grouped[key]) grouped[key] = [];
    grouped[key].push({ number: bet.bet_number, amount: parseFloat(bet.amount).toFixed(2) });
  });

  Object.entries(grouped).forEach(([label, items]) => {
    const wrapper = document.createElement('div');
    wrapper.className = 'mb-4';

    const header = document.createElement('div');
    header.className = 'text-sm text-white font-semibold mb-1';
    header.textContent = label;
    wrapper.appendChild(header);

    items.forEach(item => {
      const container = document.createElement('div');
      container.className = 'flex items-center space-x-2 mb-2';

      // Number Digits
        const betRow = document.createElement('div');
        betRow.className = 'flex items-center space-x-2 mb-1'; // Container for each full bet row

        // Digits (e.g., 1 2 3)
        const digitsWrap = document.createElement('div');
        digitsWrap.className = 'flex space-x-1';
        for (let char of item.number) {
          const digitDiv = document.createElement('div');
          digitDiv.className = 'w-6 h-6 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center text-sm font-bold text-black dark:text-white';
          digitDiv.textContent = char;
          digitsWrap.appendChild(digitDiv);
        }

        // Amount (e.g., ₱50)
        const amountDiv = document.createElement('div');
        amountDiv.className = 'text-sm text-white font-semibold';
        amountDiv.textContent = `₱${item.amount}`;

        // Append both to the row
        betRow.appendChild(digitsWrap);
        betRow.appendChild(amountDiv);

        // Append to your parent container
        container.appendChild(betRow);

    });

    livePreviewList.appendChild(wrapper);
  });
}




    async function renderSummary(numbers, gameType, drawTime) {
      summaryList.innerHTML = '';
      allBets = [];
      const adjustedDate = getAdjustedGameDate();
      const lockedNumbers = [];
      for (const number of numbers) {
        const isLocked = await isHotPickLocked(number, gameType, drawTime, adjustedDate);
        if (isLocked) {
          lockedNumbers.push(number);
          continue;
        }
        const stubId = generateStubID(gameType, drawTime);
        const bet = {
          stub_id: stubId,
          game_type: gameType,
          game_draw: drawTime,
          bet_number: number,
          amount: null,
          game_date: adjustedDate
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
      }
      if (lockedNumbers.length > 0) {
        alert(`🔥 Hot Pick Notice:
      The following numbers are locked and cannot be submitted:
      ${lockedNumbers.join(', ')}`);
      
      }
    }

    // ========== ✅ CORRECTLY SCOPED EVENT LISTENERS ==========
    // Open Modal
    if (openModalBtn) {
      openModalBtn.addEventListener('click', (e) => {
        e.preventDefault();
        betModal.classList.remove('hidden');
        goToStep(1);
        updateAvailableGamesForToday();
        // Reset form state
        betNumberInput.value = '';
        permutationList.innerHTML = '';
        summaryList.innerHTML = '';
        selectedPermutations = [];
        allBets = [];
        successAlert.classList.add('hidden');
        submitBetBtn.classList.remove('hidden');
        addAnotherBetBtn.classList.add('hidden');
        printReceiptBtn.classList.add('hidden');
        document.getElementById('print-hint')?.remove();
      });
    }

    // Cancel Modal (Closes only when clicked)
    if (cancelModalBtn) {
      cancelModalBtn.addEventListener('click', (e) => {
        e.preventDefault();
        betModal.classList.add('hidden');
        refreshDashboard();
      });
    }

    // Add Another Bet
    if (addAnotherBetBtn) {
      addAnotherBetBtn.addEventListener('click', () => {
        // Reset only the bet form, keep modal open
        betNumberInput.value = '';
        permutationList.innerHTML = '';
        summaryList.innerHTML = '';
        selectedPermutations = [];
        allBets = [];
        successAlert.classList.add('hidden');
        submitBetBtn.classList.remove('hidden');
        addAnotherBetBtn.classList.add('hidden');
        printReceiptBtn.classList.add('hidden');
        document.getElementById('print-hint')?.remove();
        goToStep(1);
        updateAvailableGamesForToday();
      });
    }

    // Print Receipt
    printReceiptBtn.addEventListener('click', () => {
      if (submittedStubIds.length > 0) {
        openBladeReceipt(submittedStubIds);

        // 🔁 After printing, reset the live preview and stub tracking
        livePreviewList.innerHTML = '';
        livePreviewSection.classList.add('hidden');
        submittedStubIds = [];
      } else {
        alert('No bets to print.');
      }
    });


    function openBladeReceipt(stubIds) {
      const url = "<?php echo e(route('agent.receipts.multi', 'stub_id_placeholder')); ?>"
        .replace('stub_id_placeholder', encodeURIComponent(stubIds.join(',')));
      window.open(url, '_blank');
    }

    // Game Type Selection
    gameTypeInputs.forEach(btn => {
      btn.addEventListener('click', () => {
        selectedGameType = btn.dataset.value;
        hiddenGameType.value = selectedGameType;
        goToStep(2);
      });
    });

    // Draw Time Selection
    drawTimeInputs.forEach(btn => {
      btn.addEventListener('click', () => {
        selectedDrawTime = btn.dataset.value;
        hiddenGameDraw.value = selectedDrawTime;
        goToStep(3);
      });
    });

    // Bet Number Input
    betNumberInput?.addEventListener('input', function () {
      const number = this.value.trim();
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

    // Next to Step 4 (Enter Amount)
    nextStep3Btn?.addEventListener('click', () => {
      if (selectedPermutations.length === 0) {
        alert('Please select at least one combination.');
        return;
      }
      renderSummary(selectedPermutations, selectedGameType, selectedDrawTime);
      goToStep(4);
    });

    // Previous Step
    prevStepBtn?.addEventListener('click', () => {
      if (currentStep > 1) goToStep(currentStep - 1);
    });

    // Submit Bet Handler
    submitBetBtn.addEventListener('click', function (e) {
      e.preventDefault();
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
          if (!res.ok) throw new Error('Network failed');
          return res.json();
        })
        .then(data => {
        if (!data.success) {
          alert('❌ Your account is deactivated.');
          return;
        }

        // Save stubs
        allBets.forEach(bet => {
          if (!submittedStubIds.includes(bet.stub_id)) {
            submittedStubIds.push(bet.stub_id);
          }
        });

        // ✅ Add to live preview
        // updateLivePreview(allBets);

        // Show success
        // successAlert.classList.remove('hidden');
        // summaryList.classList.add('hidden');
        submitBetBtn.classList.add('hidden');
        addAnotherBetBtn.classList.remove('hidden');
        printReceiptBtn.classList.remove('hidden');

          
          // //live preview portion
          const livePreviewSection = document.getElementById('live-preview');
          const livePreviewList = document.getElementById('live-preview-list');

          // Show section if hidden
          livePreviewSection.classList.remove('hidden');

          // Append each new bet to the live preview list
          allBets.forEach(bet => {
            
            let drawTime = '';
            if (bet.game_draw === '14') drawTime = '2PM';
            else if (bet.game_draw === '17') drawTime = '5PM';
            else if (bet.game_draw === '21') drawTime = '9PM';
            else drawTime = bet.game_draw;

            const li = document.createElement('li');
            li.className = 'list-none'; 
            li.innerHTML = `
              <div class="flex justify-between mb-2">
                <span class="flex items-center gap-1">
                  <strong>${bet.game_type}</strong> (${drawTime})
                  <span class="flex gap-1 ml-2">
                    ${bet.bet_number.split('').map(num => `
                      <span class="w-6 h-6 rounded-full bg-yellow-500 text-center text-md flex items-center justify-center dark:text-black">
                        ${num}
                      </span>
                    `).join('')}
                  </span>
                </span>
                <span>₱${bet.amount.toFixed(2)}</span>
              </div>
            `;
            livePreviewList.appendChild(li);
          });

          // let hint = document.getElementById('print-hint');
          // if (!hint) {
          //   hint = document.createElement('div');
          //   hint.id = 'print-hint';
          //   hint.className = 'mt-2 text-sm text-green-600 dark:text-green-400';
          //   summaryList.parentNode.insertBefore(hint, summaryList.nextSibling);
          // }
          // hint.textContent = '✅ Bet saved! You may now print the receipt or add another bet.';
        })
        .catch(err => {
          console.error('Submission error:', err);
          alert('Hot Pick Number. Sold Out! Please choose another.');
          goToStep(1);
        });
        
    });

    // Refresh Dashboard
    function refreshDashboard() {
      fetch('/agent/dashboard/gross-total')
        .then(res => res.json())
        .then(data => {
          const grossEl = document.getElementById('dashboard-gross');
          if (grossEl) {
            grossEl.textContent = `₱${parseFloat(data.total_gross).toFixed(2)}`;
          }
        })
        .catch(err => console.error('Dashboard error:', err));
    }

    // Update available games based on day
    function updateAvailableGamesForToday() {
      const today = new Date().toLocaleDateString('en-US', { weekday: 'long' });
      const available = gameScheduleByDay[today] || [];
      gameTypeInputs.forEach(btn => {
        const type = btn.dataset.value;
        btn.disabled = !available.includes(type);
        btn.classList.toggle('opacity-50', !available.includes(type));
      });
    }

    // Initialize on load
    updateAvailableGamesForToday();
  });
</script><?php /**PATH /home/u355685815/domains/orcasbettingapp.com/resources/views/partials/bet-modal-script.blade.php ENDPATH**/ ?>