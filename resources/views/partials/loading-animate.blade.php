<script>
  const syncButton = document.getElementById('sync-offline-bets');
  const syncIcon = document.getElementById('sync-icon');

  syncButton.addEventListener('click', async () => {
    // Add spinning and disable button
    syncIcon.classList.add('spin');
    syncButton.classList.add('disabled');

    try {
      // Simulate a loading process (e.g., fetch/post request)
        await fetch('/sync-offline-bets', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
            });

      // You can insert your actual sync logic here, e.g.:
      // await fetch('/api/sync-offline-bets');

    } catch (error) {
      console.error('Sync failed', error);
    } finally {
      // Remove spinning and enable button again
      syncIcon.classList.remove('spin');
      syncButton.classList.remove('disabled');
    }
  });



</script>