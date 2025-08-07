<script>
    function showToast(message, type = 'success') {
  window.dispatchEvent(new CustomEvent('toast', {
    detail: { message, type }
  }));
}
</script>