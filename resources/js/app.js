import Alpine from 'alpinejs';
import { Plugins } from '@capacitor/core';
const { BluetoothPrinterPlugin } = Plugins;

window.Alpine = Alpine;

Alpine.store('layout', {
  darkMode: JSON.parse(localStorage.getItem('darkMode') || 'false'),
  sidebarCollapsed: JSON.parse(localStorage.getItem('sidebarCollapsed') || 'false'),
  mobileSidebarCollapsed: false,
  windowWidth: window.innerWidth,
  lastScrollTop: 0,
  scrollingDown: false,

  // Replace these with your actual agent ID and auth token
  agentId: 'your-agent-id',
  authToken: 'your-auth-token',

  get isMobile() {
    return this.windowWidth < 640; // Tailwind's sm breakpoint
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

  async setupPrinter() {
    try {
      // Request permissions if you want to handle them here (optional)
      // You may want to call Capacitor's Permissions plugin here if needed

      // Fetch and save printer MAC from backend
      await BluetoothPrinterPlugin.fetchPrinterMac({
        agentId: this.agentId,
        token: this.authToken,
      });
      console.log('Printer MAC fetched and saved');
    } catch (e) {
      console.error('Failed to fetch printer MAC:', e);
    }
  },

  async selectPrinterManually() {
    try {
      const result = await BluetoothPrinterPlugin.selectPrinter();
      console.log('Selected printer MAC:', result.selectedMac);
      alert('Printer selected: ' + result.selectedMac);
    } catch (e) {
      console.error('Failed to select printer:', e);
      alert('Failed to select printer: ' + e.message);
    }
  },

  init() {
    document.documentElement.classList.toggle('dark', this.darkMode);

    // Hide sidebar on mobile by default
    if (this.isMobile) {
      this.mobileSidebarCollapsed = true;
    }

    window.addEventListener('resize', () => {
      this.windowWidth = window.innerWidth;
    });

    window.addEventListener('scroll', () => this.handleScroll());

    // Call printer setup on app load
    this.setupPrinter();
  }
});

Alpine.start();
