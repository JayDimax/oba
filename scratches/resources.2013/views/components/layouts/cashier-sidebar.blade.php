
<!-- Sidebar -->
<aside
  x-data="{
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
    'w-16 sm:w-20': sidebarCollapsed || windowWidth < 640,
    'w-72': !sidebarCollapsed && windowWidth >= 640
  }"
  class="sticky bottom-0 left-0 transform
         bg-white dark:bg-gray-800 shadow-md
         transition-all duration-300 ease-in-out flex flex-col items-center px-6 py-4"
>

  <!-- Sidebar Toggle Button -->
  <div class="w-full flex justify-end px-3 py-2">
    <button @click="toggleSidebar" class="text-gray-600 dark:text-gray-300 hover:text-blue-600 dark:hover:text-white transition">
      <i :data-lucide="sidebarCollapsed ? 'chevron-right' : 'chevron-left'" class="w-5 h-5"></i>
    </button>
  </div>

  <!-- Profile Info -->
  <div class="mt-6 mb-8 text-center" x-show="!sidebarCollapsed" x-transition>
    @include('cash-partials.upload')

    <h2 class="text-lg font-bold">{{ auth()->user()->name }}</h2>
    <p class="text-sm text-gray-500">{{ '@' . auth()->user()->cashier_code }}</p>
    <p class="text-sm text-gray-500">{{ auth()->user()->cashier?->phone ?? 'N/A' }}</p>
  </div>

  <!-- Navigation -->
  <nav class="flex-1 space-y-3 w-full">
    @php
      $navItems = [
        ['label' => 'Dashboard', 'icon' => 'home', 'route' => 'cashier.dashboard'],
        ['label' => 'Bet', 'icon' => 'gamepad', 'route' => 'cashier.bet'],
        ['label' => 'Bet History', 'icon' => 'clock', 'route' => 'cashier.bet.history'],
        ['label' => 'Winning Bets', 'icon' => 'star', 'route' => 'cashier.winning'],
        ['label' => 'Results', 'icon' => 'layers', 'route' => 'cashier.results'],
        ['label' => 'Reports', 'icon' => 'file-text', 'route' => 'cashier.reports'],
        ['label' => 'Collections', 'icon' => 'archive', 'route' => 'cashier.collections'],
        ['label' => 'Support', 'icon' => 'help-circle', 'route' => 'cashier.support'],
        ['label' => 'Settings', 'icon' => 'settings', 'route' => 'cashier.settings'],
      ];
    @endphp

    @foreach($navItems as $item)
      @php $isActive = request()->routeIs($item['route']); @endphp
      <a href="{{ route($item['route']) }}"
         class="flex items-center px-3 py-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition"
         :class="{ 'justify-center': sidebarCollapsed || windowWidth < 640 }">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          {!! $svgIcons[$item['icon']] ?? '' !!}
        </svg>
        <span class="ml-3" x-show="!sidebarCollapsed && windowWidth >= 640" x-transition>{{ $item['label'] }}</span>
      </a>
    @endforeach
  </nav>

  <!-- Logout & Version -->
  <div class="mt-6 text-sm text-gray-400 w-full text-center">
    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button class="flex items-center gap-2 text-red-600 hover:text-red-800 justify-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 0 1-6 0V7a3 3 0 0 1 6 0v1"/>
        </svg>
        <span x-show="!sidebarCollapsed && windowWidth >= 640" x-transition>Logout</span>
      </button>
    </form>
    <p class="text-xs mt-4" x-show="!sidebarCollapsed && windowWidth >= 640">JBD v1.0.0</p>
  </div>
</aside>


    <!-- Main Content -->
    <main class="flex-1 p-4 overflow-auto">
      {{ $slot }}
    </main>

  </div>
     @include('cash-partials.betting-enhanced')
      @include('cash-partials.bet-modal')
      @include('cash-partials.toggle')
      @include('cash-partials.bet-modal-script')
      @include('cash-partials.offlinebets')
      @include('components.alert-toast')
</body>
</html>
