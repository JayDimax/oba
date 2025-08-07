<!DOCTYPE html>
<html lang="en" x-data="layout()" x-init="init()" x-cloak x-bind:class="{ 'dark': darkMode }">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $title ?? 'Agent Panel' }}</title>
  @vite(['resources/css/app.css', 'resources/js/app.js']) <!-- Adjust if using mix -->
  <script src="https://unpkg.com/alpinejs" defer></script>
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
  <style>
    #print-area {
      display: none;
    }
    @media print {
      body * {
        visibility: hidden;
      }
      #print-area, #print-area * {
        visibility: visible;
      }
      #print-area {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
      }
    }
  </style>
</head>
<body class="flex bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen">

<aside
  :class="{
    'w-16 sm:w-20': sidebarCollapsed,
    'w-72': !sidebarCollapsed,
    'translate-x-0': sidebarOpen || windowWidth >= 640,
    '-translate-x-full': !sidebarOpen && windowWidth < 640
  }"
  class="sticky bottom-0 left-0 transform bg-white dark:bg-gray-800 shadow-md transition-all duration-300 ease-in-out flex flex-col items-center z-20"
>
    <!-- Toggle Button -->
    <div class="w-full flex justify-end px-3 py-2">
      <button @click="toggleSidebar" class="text-gray-600 dark:text-gray-300 hover:text-blue-600 dark:hover:text-white transition">
        <i :data-lucide="sidebarCollapsed ? 'menu' : 'menu'" class="w-5 h-5"></i>
      </button>
    </div>

    <!-- Profile Section -->
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
        class="group flex items-center gap-3 px-3 py-2 rounded-md transition-all duration-200 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200">
        <i :data-lucide="$store.layout.darkMode ? 'sun' : 'moon'" class="w-5 h-5"></i>
        <span class="truncate transition-all duration-300 ease-in-out" x-show="!sidebarCollapsed" x-transition>
          Mode
        </span>
      </a>
    </nav>

    <!-- Logout -->
    <div class="mt-6 text-sm text-gray-400 flex items-center justify-between px-2 w-full">
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="flex items-center gap-2 text-white hover:text-red-400 px-2 py-2 rounded transition-all duration-200">
          <i data-lucide="log-out" class="w-5 h-5"></i>
          <span x-show="!sidebarCollapsed" x-transition>Logout</span>
        </button>
      </form>
      <p class="text-xs hidden sm:block">JBD v1.0.0</p>
    </div>
  </aside>

  <!-- Main Content -->
  <main
    :class="sidebarCollapsed ? 'ml-16 sm:ml-20' : 'ml-72'"
    class="flex-1 transition-all duration-300 p-4 sm:p-6 bg-white dark:bg-gray-900 text-gray-900 dark:text-white"
  >
    {{ $slot }}
  </main>

  <!-- Scripts -->
<script>
  function layout() {
    return {
      sidebarOpen: true,
      sidebarCollapsed: false,
      darkMode: localStorage.getItem('theme') === 'dark',
      windowWidth: window.innerWidth,
      init() {
        window.addEventListener('resize', () => {
          this.windowWidth = window.innerWidth;
          // Optionally auto collapse sidebar on small screens
          if (this.windowWidth < 640) {
            this.sidebarOpen = false;
          } else {
            this.sidebarOpen = true;
          }
        });
        this.syncDarkMode();
        this.renderIcons();
      },
      toggleSidebar() {
        this.sidebarCollapsed = !this.sidebarCollapsed;
        this.renderIcons();
      },
      toggleDarkMode() {
        this.darkMode = !this.darkMode;
        localStorage.setItem('theme', this.darkMode ? 'dark' : 'light');
        this.renderIcons();
      },
      syncDarkMode() {
        document.documentElement.classList.toggle('dark', this.darkMode);
      },
      renderIcons() {
        setTimeout(() => lucide.createIcons(), 10);
      }
    }
  }
</script>

@include('partials.betting-enhanced')
@include('partials.bet-modal')
@include('partials.toggle')
@include('partials.bet-modal-script')
@include('partials.offlinebets')
@include('components.alert-toast')
</body>
</html>
