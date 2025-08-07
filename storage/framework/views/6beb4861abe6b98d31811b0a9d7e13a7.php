<script>
  document.addEventListener('DOMContentLoaded', function () {
    const toggleBtn = document.getElementById('darkModeToggle');
    const htmlTag = document.documentElement;

    if (!toggleBtn) return;

    const savedTheme = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

    if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
      htmlTag.classList.add('dark');
      toggleBtn.textContent = '☀️';
    } else {
      htmlTag.classList.remove('dark');
      toggleBtn.textContent = '🌙';
    }

    toggleBtn.addEventListener('click', () => {
      const isDark = htmlTag.classList.toggle('dark');
      localStorage.setItem('theme', isDark ? 'dark' : 'light');
      toggleBtn.textContent = isDark ? '☀️' : '🌙';
    });
    
  });
</script><?php /**PATH /home/u355685815/domains/orcasbettingapp.com/resources/views/cash-partials/toggle.blade.php ENDPATH**/ ?>