<script>
  function triggerCutoffAlert() {
    const alertAudio = document.getElementById('cutoff-audio');
    const alpineRoot = document.querySelector('[x-data]');

    if (alertAudio && alpineRoot && Alpine) {
      Alpine.store('cutoffAlert', true); // Optional Alpine store
      alpineRoot.__x.$data.showCutoffAlert = true;
      alertAudio.play();
    }
  }

  // Example: Trigger at a specific time or condition
  setTimeout(() => {
    triggerCutoffAlert();
  }, 2000); // replace with actual cutoff detection logic
</script>
