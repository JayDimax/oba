<!-- Sidebar -->
<aside
  x-data="{
    sidebarOpen: true,
    sidebarCollapsed: JSON.parse(localStorage.getItem('sidebarCollapsed') || 'false'),
    windowWidth: window.innerWidth,
    updateWidth() {
      this.windowWidth = window.innerWidth;
    },
    toggleSidebar() {
      this.sidebarCollapsed = !this.sidebarCollapsed;
      localStorage.setItem('sidebarCollapsed', this.sidebarCollapsed);
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
  class="sticky bottom-0 left-0 transform
         bg-white dark:bg-gray-800 shadow-md z-40
         transition-all duration-300 ease-in-out flex flex-col items-center"
>

  <!-- Sidebar Toggle Button -->
  <div class="w-full flex justify-end px-3 py-2">
    <button @click="toggleSidebar" class="text-gray-600 dark:text-gray-300 hover:text-blue-600 dark:hover:text-white transition">
      <i :data-lucide="sidebarCollapsed ? 'menu' : 'menu'" class="w-5 h-5"></i>
    </button>
  </div>

  <!-- Profile Info -->
  <div class="mb-6 sm:block hidden" x-show="!sidebarCollapsed" x-transition>
    <div class="text-center">
      <!-- @include('partials.upload') -->
      <h2 class="text-sm font-bold truncate">{{ auth()->user()->name }}</h2>
      <p class="text-xs text-gray-500 truncate">{{ auth()->user()->cashier?->employee_code ?? 'N/A' }}</p>
      <p class="text-xs text-gray-500 truncate">{{ auth()->user()->phone ?? 'N/A' }}</p>
    </div>
  </div>

  <!-- Navigation -->
  <nav class="flex-1 space-y-1 w-full px-2">
    @php
      $navItems = [
        ['label' => 'Dashboard',   'icon' => 'layout-dashboard', 'route' => 'cashier.dashboard'],
        ['label' => 'Approval',   'icon' => 'sent', 'route' => 'cashier.pending'],
        ['label' => 'Remittance',  'icon' => 'credit-card',      'route' => 'cashier.remittance'],
        ['label' => 'Receipts',    'icon' => 'file-text',        'route' => 'cashier.receipts.index'],
        ['label' => 'Reports',     'icon' => 'bar-chart-2',      'route' => 'cashier.reports'],
   
      ];
    @endphp

    @foreach($navItems as $item)
      @php $isActive = request()->routeIs($item['route']) || request()->routeIs($item['route'] . '.*'); @endphp
      <a href="{{ route($item['route']) }}"
         class="flex items-center gap-3 px-3 py-2 rounded-md transition-all duration-200
         {{ $isActive
            ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-white font-semibold'
            : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200' }}">
        <i data-lucide="{{ $item['icon'] }}" class="w-5 h-5"></i>
        <span class="truncate transition-all duration-300 ease-in-out" x-show="!sidebarCollapsed" x-transition>
          {{ $item['label'] }}
        </span>
      </a>
    @endforeach

    <!-- Dark Mode Toggle -->
    <a href="javascript:void(0)"
       @click="$store.layout.toggleDarkMode()"
       class="group flex items-center gap-3 px-3 py-2 rounded-md transition-all duration-200 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200">
      <i :data-lucide="$store.layout.darkMode ? 'sun' : 'moon'" class="w-5 h-5"></i>
      <span class="truncate transition-all duration-300 ease-in-out" x-show="!sidebarCollapsed" x-transition>
        Mode
      </span>
    </a>

    <!-- Logout -->
    <div class="mt-6 text-sm text-gray-400 flex items-center justify-between px-2 w-full">
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="flex items-center gap-2 text-black hover:text-red-400 px-2 py-2 rounded transition-all duration-200">
          <i data-lucide="log-out" class="w-5 h-5"></i>
          <span x-show="!sidebarCollapsed" x-transition>Logout</span>
        </button>
      </form>
    </div>

    <p class="text-xs text-center hidden sm:block">v1.0.0</p>
  </nav>
</aside>
