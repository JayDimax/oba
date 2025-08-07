<script>
    // === betting-enhanced.js ===
// Includes: Cutoff logic + offline storage + manual/auto sync

document.addEventListener('DOMContentLoaded', function () {
  const drawTimeInputs = document.querySelectorAll('.draw-time-btn');
  const openModalBtn = document.getElementById('open-bet-modal');
  const syncBtn = document.getElementById('sync-offline-bets');

  // === TIME CUT-OFF VALIDATION ===
function getPhilippineTime() {
  return new Date(); // already in your system's timezone
}

  function getCutoffTime(drawCode) {
    const now = getPhilippineTime();
    const cutoff = new Date(now);
    if (drawCode === '14') cutoff.setHours(13, 50, 0);  // 1:50 PM
    if (drawCode === '17') cutoff.setHours(16, 50, 0);  // 4:50 PM
    if (drawCode === '21') cutoff.setHours(20, 50, 0);  // 8:50 PM
    return cutoff;
  }

  function applyDrawCutoffFilter() {
    const now = getPhilippineTime();
    drawTimeInputs.forEach(btn => {
      const code = btn.dataset.value;
      const cutoff = getCutoffTime(code);
      if (now >= cutoff) {
        btn.disabled = true;
        btn.classList.add('opacity-50', 'cursor-not-allowed');
        btn.title = 'Cutoff passed';
      } else {
        btn.disabled = false;
        btn.classList.remove('opacity-50', 'cursor-not-allowed');
        btn.title = '';
      }
    });
  }

  if (openModalBtn) {
    openModalBtn.addEventListener('click', () => {
      applyDrawCutoffFilter();
    });
  }

  // === OFFLINE BET STORAGE ===
  function saveOfflineBet(betObj) {
    let offline = JSON.parse(localStorage.getItem('offlineBets') || '[]');
    offline.push(betObj);
    localStorage.setItem('offlineBets', JSON.stringify(offline));
  }

  function trySyncOfflineBets() {
    if (!navigator.onLine) return;
    let offline = JSON.parse(localStorage.getItem('offlineBets') || '[]');
    if (offline.length === 0) return;

    fetch('/bets/sync', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({ bets: offline })
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        alert(`✅ Synced ${offline.length} bet(s).`);
        localStorage.removeItem('offlineBets');
      } else {
        alert('⚠️ Sync failed. Try again later.');
      }
    })
    .catch(() => alert('⚠️ Network/server error during sync.'));
  }

  if (syncBtn) {
    syncBtn.addEventListener('click', trySyncOfflineBets);
  }

  // Auto sync on reconnect
  window.addEventListener('online', trySyncOfflineBets);

  // === HOOK INTO BET FORM SUBMIT ===
  const betForm = document.getElementById('bet-form');
  const submitBtn = document.getElementById('submit-bet');
  if (betForm) {
    betForm.addEventListener('submit', function (e) {
      e.preventDefault();
      const payload = { bets: window.allBets };
      submitBtn.disabled = true;

      fetch('/bets/store', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(payload)
      })
      .then(res => {
        if (!res.ok) throw new Error('Offline fallback');
        return res.json();
      })
      .then(data => {
        if (data.success) {
          alert('✅ Bet successfully submitted.');
          // proceed as normal
        } else {
          throw new Error('Rejected');
        }
      })
      .catch(err => {
        // alert('📴 Saving bet offline.');
        window.allBets.forEach(bet => saveOfflineBet(bet));
      })
      .finally(() => submitBtn.disabled = false);
    });
  }
});

</script>