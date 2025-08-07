<script>
    function toggleProfileMenu() {
      const menu = document.getElementById('profileDropdown');
      menu.classList.toggle('hidden');
    }

    // Optional: Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
      const button = document.getElementById('profileMenuButton');
      const dropdown = document.getElementById('profileDropdown');
      if (!button.contains(event.target) && !dropdown.contains(event.target)) {
        dropdown.classList.add('hidden');
      }
    });
</script>