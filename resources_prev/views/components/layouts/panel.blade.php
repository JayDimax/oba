<!DOCTYPE html>
<html lang="en" x-data x-init="$store.layout.init()" :class="{ 'dark': $store.layout.darkMode }">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $title ?? config('app.name') }}</title>

  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  <script src="https://unpkg.com/lucide@latest" defer></script>
  <script>
    document.addEventListener("DOMContentLoaded", () => lucide.createIcons());
  </script>

  <style>
    [x-cloak] { display: none !important; }
    html, body { overflow-x: hidden; }
    @media (max-width: 639px) {
      .sidebar-mobile-offset { top: 3rem; }
    }
  </style>
</head>
<body class="bg-white dark:bg-gray-900 text-gray-900 dark:text-white font-sans">

  <!-- Overlay when mobile sidebar is open -->
  <div x-show="sidebarOpen && windowWidth < 640"
       @click="sidebarOpen = false"
       class="fixed inset-0 z-30 bg-black bg-opacity-50 sm:hidden"
       x-transition.opacity
       x-cloak>
  </div>

  <div class="flex min-h-screen" >
    {{-- Sidebar Slot --}}
    {{ $sidebar }}

    {{-- Main Content --}}
    <main
      x-cloak
      :class="{
        'sm:ml-16 sm:ml-20': sidebarCollapsed && windowWidth >= 640,
        'sm:ml-72': !sidebarCollapsed && windowWidth >= 640
      }"
      class="flex-1 transition-all duration-300 p-4 sm:p-6 bg-white dark:bg-gray-900 text-gray-900 dark:text-white"
    >
      {{ $slot }}
    </main>
  </div>

  @include('partials.betting-enhanced')
  @include('partials.bet-modal')
  @include('partials.toggle')
  @include('partials.bet-modal-script')
  {{-- @include('partials.offlinebets') --}}
  @include('components.alert-toast')
</body>
</html>
