<!-- Sidebar -->
<aside
  x-data="{
    sidebarOpen: true,
    sidebarCollapsed: false,
    windowWidth: window.innerWidth,
    updateWidth() {
      this.windowWidth = window.innerWidth;
    },
    toggleSidebar() {
      this.sidebarCollapsed = !this.sidebarCollapsed;
    }
  }"
  x-init="window.addEventListener('resize', () => updateWidth())"
  x-cloak
  :class="{
    'translate-x-0': sidebarOpen || windowWidth >= 640,
    '-translate-x-full': !sidebarOpen && windowWidth < 640,
    'w-16 sm:w-20': sidebarCollapsed,
    'w-72': !sidebarCollapsed
  }"
  class="transform min-h-screen bg-white dark:bg-gray-900 shadow-md transition-all duration-300 ease-in-out flex flex-col items-center border-r dark:border-gray-700"
>


  <!-- Sidebar Toggle Button -->
  <div class="w-full flex justify-end px-3 py-2">
    <button @click="toggleSidebar" class="text-gray-600 dark:text-gray-300 hover:text-blue-600 dark:hover:text-white transition">
      <i :data-lucide="sidebarCollapsed ? 'menu' : 'menu'" class="w-5 h-5"></i>
    </button>
  </div>

  <!-- Profile (only when expanded) -->
  <div class="mb-6 sm:block hidden" x-show="!sidebarCollapsed" x-transition>
    <div class="text-center">
      
      <h2 class="text-sm font-bold truncate">{{ auth()->user()->name }}</h2>
      <p class="text-xs text-gray-500 truncate">{{ '@' . auth()->user()->agent_code }}</p>
      <p class="text-xs text-gray-500 truncate">{{ auth()->user()->agent?->phone ?? 'N/A' }}</p>
    </div>
  </div>

  <!-- Navigation -->
  <nav class="flex-1 space-y-1 w-full px-2">
    @php
      $navItems = [
        ['label' => 'Dashboard', 'icon' => 'layout-dashboard', 'route' => 'agent.dashboard'],
        ['label' => 'Bet History', 'icon' => 'history', 'route' => 'agent.bet.history'],
        ['label' => 'Winning Bets', 'icon' => 'trophy', 'route' => 'agent.winning'],
        ['label' => 'Results', 'icon' => 'list', 'route' => 'agent.results'],
        ['label' => 'Reports', 'icon' => 'file-text', 'route' => 'agent.reports'],
        ['label' => 'Collections', 'icon' => 'archive', 'route' => 'agent.collections'],
        ['label' => 'Support', 'icon' => 'help-circle', 'route' => 'agent.support'],
        ['label' => 'Profile', 'icon' => 'user', 'route' => 'agent.agent-edit'],
      ];
    @endphp

    @foreach($navItems as $item)
      @php $isActive = request()->routeIs($item['route']); @endphp
      <a href="{{ route($item['route']) }}"
         class="flex items-center gap-3 px-3 py-2 rounded-md transition-all duration-200
         {{ $isActive ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-white font-semibold' : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200' }}">
        <i data-lucide="{{ $item['icon'] }}" class="w-5 h-5"></i>
        <span class="truncate transition-all duration-300 ease-in-out" x-show="!sidebarCollapsed" x-transition>
          {{ $item['label'] }}
        </span>
      </a>
    @endforeach

    <!-- Dark Mode Toggle -->
<a href="javascript:void(0)"
   @click="$store.layout.toggleDarkMode()"
   class="group relative flex items-center gap-3 px-3 py-2 rounded-md transition-all duration-200 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200">
  <i :data-lucide="$store.layout.darkMode ? 'sun' : 'moon'" class="w-5 h-5"></i>
  <span class="truncate transition-all duration-300 ease-in-out" x-show="!sidebarCollapsed" x-transition>
    Mode
  </span>
</a>


      
    <!-- Logout -->
    <div class="mt-6 text-sm text-gray-400 flex items-center justify-between px-2 w-full">
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="flex items-center gap-3 px-3 py-2 rounded-md transition-all duration-200 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200">
          <i data-lucide="log-out" class="w-5 h-5"></i>
          <span x-show="!sidebarCollapsed" x-transition>Logout</span>
        </button>
      </form>
    </div>
    <p class="text-xs text-center hidden sm:block">v1.0.0</p>
  </nav>
</aside>
