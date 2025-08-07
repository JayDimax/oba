<script>
    function showToast(message, type = 'success') {
  window.dispatchEvent(new CustomEvent('toast', {
    detail: { message, type }
  }));
}
</script><?php /**PATH /home/u355685815/domains/orcasbettingapp.com/resources/views/components/alert-toast.blade.php ENDPATH**/ ?>