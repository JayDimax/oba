<script>
    function showToast(message, type = 'success') {
  window.dispatchEvent(new CustomEvent('toast', {
    detail: { message, type }
  }));
}
</script><?php /**PATH F:\laragon\www\oba\resources\views/components/alert-toast.blade.php ENDPATH**/ ?>