// layoutStore.js
document.addEventListener('alpine:init', () => {
  Alpine.store('layout', {
    darkMode: JSON.parse(localStorage.getItem('darkMode') || 'false'),
    sidebarCollapsed: JSON.parse(localStorage.getItem('sidebarCollapsed') || 'false'),
    mobileSidebarCollapsed: false,
    windowWidth: window.innerWidth,
    lastScrollTop: 0,
    scrollingDown: false,

    get isMobile() {
      return this.windowWidth < 640;
    },

    toggleDarkMode() {
      this.darkMode = !this.darkMode;
      localStorage.setItem('darkMode', this.darkMode);
      document.documentElement.classList.toggle('dark', this.darkMode);
    },

    toggleSidebar() {
      if (this.isMobile) {
        this.mobileSidebarCollapsed = !this.mobileSidebarCollapsed;
      } else {
        this.sidebarCollapsed = !this.sidebarCollapsed;
        localStorage.setItem('sidebarCollapsed', this.sidebarCollapsed);
      }
    },

    handleScroll() {
      const st = window.pageYOffset || document.documentElement.scrollTop;
      this.scrollingDown = st > this.lastScrollTop;
      this.lastScrollTop = st <= 0 ? 0 : st;
    },

    init() {
      document.documentElement.classList.toggle('dark', this.darkMode);

      if (this.isMobile) {
        this.mobileSidebarCollapsed = true;
      }

      window.addEventListener('resize', () => {
        this.windowWidth = window.innerWidth;
      });

      window.addEventListener('scroll', () => this.handleScroll());
    }
  });
});
