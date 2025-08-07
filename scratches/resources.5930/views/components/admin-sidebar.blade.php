<!-- Sidebar -->
<aside
  x-data
  x-init="
    $watch('$store.layout.windowWidth', () => lucide.createIcons());
    $watch('$store.layout.sidebarCollapsed', () => lucide.createIcons());
  "
  x-cloak
  x-show="!$store.layout.isMobile || !$store.layout.mobileSidebarCollapsed"
  x-transition
  :class="{
    'w-16 sm:w-20': $store.layout.sidebarCollapsed,
    'w-72': !$store.layout.sidebarCollapsed
  }"
  class="bg-white dark:bg-gray-800 min-h-screen transition-all duration-300 ease-in-out flex flex-col z-50 border-r dark:border-gray-700"
>



  <!-- Sidebar Toggle Button -->
  <div class="p-4">
    <button
      @click="$store.layout.toggleSidebar()"
      class="text-gray-600 dark:text-gray-300 hover:text-blue-600 dark:hover:text-white transition"
    >
      <i data-lucide="menu" class="w-5 h-5"></i>
    </button>
  </div>

  <!-- Profile (only when expanded) -->
  <div class="mb-6 px-4 sm:block hidden" x-show="!$store.layout.sidebarCollapsed" x-transition>
    <div class="text-center">
      <h2 class="text-sm font-bold truncate">{{ auth()->user()->name }}</h2>
      <p class="text-xs text-gray-500 truncate">{{ '@' . auth()->user()->agent_code }}</p>
      <p class="text-xs text-gray-500 truncate">{{ auth()->user()->agent?->phone ?? 'N/A' }}</p>
    </div>
  </div>

  <!-- Navigation -->
  <nav class="flex-1 space-y-1 w-full px-2">
    <a href="{{ route('admin.admindashboard') }}"
       class="flex items-center gap-3 px-3 py-2 rounded-md transition hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200">
      <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
      <span class="truncate" x-show="!$store.layout.sidebarCollapsed" x-transition>Dashboard</span>
    </a>
    <a href="{{ route('admin.users.index') }}"
       class="flex items-center gap-3 px-3 py-2 rounded-md transition hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200">
      <i data-lucide="users" class="w-5 h-5"></i>
      <span class="truncate" x-show="!$store.layout.sidebarCollapsed" x-transition>Users</span>
    </a>
    <a href="{{ route('admin.results.index') }}"
       class="flex items-center gap-3 px-3 py-2 rounded-md transition hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200">
      <i data-lucide="send" class="w-5 h-5"></i>
      <span class="truncate" x-show="!$store.layout.sidebarCollapsed" x-transition>Results</span>
    </a>
    <a href="{{ route('admin.reports.index') }}"
       class="flex items-center gap-3 px-3 py-2 rounded-md transition hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200">
      <i data-lucide="file-text" class="w-5 h-5"></i>
      <span class="truncate" x-show="!$store.layout.sidebarCollapsed" x-transition>Reports</span>
    </a>
    <a href="{{ route('admin.settings.index') }}"
       class="flex items-center gap-3 px-3 py-2 rounded-md transition hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200">
      <i data-lucide="settings" class="w-5 h-5"></i>
      <span class="truncate" x-show="!$store.layout.sidebarCollapsed" x-transition>Settings</span>
    </a>
  </nav>

  <!-- Version Info -->
  <p class="text-xs text-left hidden sm:block px-4 py-2">JBDv1.0.0</p>
</aside>
