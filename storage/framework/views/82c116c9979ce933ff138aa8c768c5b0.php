  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const toggleBtn = document.getElementById('darkModeToggle');
      const htmlTag = document.documentElement;

      // Initialize based on saved preference
      if (localStorage.getItem('theme') === 'dark') {
        htmlTag.classList.add('dark');
        toggleBtn.textContent = '☀️';
      }

      toggleBtn.addEventListener('click', () => {
        if (htmlTag.classList.contains('dark')) {
          htmlTag.classList.remove('dark');
          localStorage.setItem('theme', 'light');
          toggleBtn.textContent = '🌙';
        } else {
          htmlTag.classList.add('dark');
          localStorage.setItem('theme', 'dark');
          toggleBtn.textContent = '☀️';
        }
      });
    });

  </script><?php /**PATH D:\laragon\www\oba\resources\views/partials/toggle.blade.php ENDPATH**/ ?>