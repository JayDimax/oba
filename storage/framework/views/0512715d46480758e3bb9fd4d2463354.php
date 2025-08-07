<script>
  document.addEventListener('DOMContentLoaded', () => {
    const syncBtn = document.getElementById('sync-offline-bets');
    const offlineKey = 'offline_bets';

    // Create toast container
    let toastContainer = document.createElement('div');
    toastContainer.id = 'toast-container';
    toastContainer.style.position = 'fixed';
    toastContainer.style.bottom = '1rem';
    toastContainer.style.right = '1rem';
    toastContainer.style.zIndex = '9999';
    toastContainer.style.display = 'flex';
    toastContainer.style.flexDirection = 'column';
    toastContainer.style.gap = '0.5rem';
    document.body.appendChild(toastContainer);

    function showToast(message, bgColor = '#2563eb') { // default blue background
      const toast = document.createElement('div');
      toast.textContent = message;
      toast.style.backgroundColor = bgColor;
      toast.style.color = 'white';
      toast.style.padding = '0.5rem 1rem';
      toast.style.borderRadius = '0.375rem';
      toast.style.boxShadow = '0 2px 6px rgba(0,0,0,0.2)';
      toast.style.fontSize = '0.875rem';
      toast.style.fontWeight = '500';
      toast.style.maxWidth = '300px';
      toast.style.opacity = '1';
      toast.style.transition = 'opacity 0.5s ease';
      toastContainer.appendChild(toast);
      setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 500);
      }, 3000);
    }

    function isOnline() {
      return navigator.onLine;
    }

    function getOfflineBets() {
      return JSON.parse(localStorage.getItem(offlineKey) || '[]');
    }

    function saveOfflineBet(bet) {
      const bets = getOfflineBets();
      bets.push(bet);
      localStorage.setItem(offlineKey, JSON.stringify(bets));
    }

    function clearOfflineBets() {
      localStorage.removeItem(offlineKey);
    }

    async function syncOfflineBets() {
      const bets = getOfflineBets();
      if (!bets.length) {
        showToast('No offline bets to sync.', '#6b7280'); // gray background
        return;
      }

      try {
        const res = await fetch('/agent/sync-bets', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ bets })
        });

        if (res.ok) {
          clearOfflineBets();
          showToast('Offline bets synced successfully!', '#16a34a'); // green background
        } else {
          showToast('Failed to sync bets.', '#dc2626'); // red background
        }
      } catch (e) {
        showToast('Error syncing bets. Check connection.', '#dc2626');
      }
    }

    syncBtn.addEventListener('click', syncOfflineBets);

    window.placeBetHandler = async function(betData) {
          const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
          if (!isOnline()) {
            saveOfflineBet(betData);
            showToast('You are offline. Bet saved locally.', '#fbbf24');
            return;
          }
        
          try {
            const res = await fetch('/api/place-bet', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
              },
              body: JSON.stringify(betData)
            });
        
            const data = await res.json();
        
            if (!res.ok) {
              console.warn('Server error:', res.status, data);
              showToast(`Server error: ${data.message || 'Failed to place bet'}`, '#ef4444');
              return;
            }
        
            showToast('Bet placed successfully!', '#16a34a');
          } catch (err) {
            console.error('Request error. Saving offline.', err);
            saveOfflineBet(betData);
            showToast('Request failed. Bet saved locally.', '#f59e0b');
          }
        }

    }
  });
</script>
<?php /**PATH /home/u355685815/domains/orcasbettingapp.com/resources/views/partials/offlinebets.blade.php ENDPATH**/ ?>