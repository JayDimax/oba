<script>
  document.addEventListener('DOMContentLoaded', function () {
    const drawTimeInputs = document.querySelectorAll('.draw-time-btn');
    const openModalBtn = document.getElementById('open-bet-modal');

    // === TIME CUT-OFF VALIDATION ===
    function getPhilippineTime() {
      return new Date(); // Assumes server is in Asia/Manila timezone
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
  });

</script><?php /**PATH D:\laragon\www\oba\resources\views/partials/betting-enhanced.blade.php ENDPATH**/ ?>