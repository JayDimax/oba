@props(['label' => 'Mode'])

<a href="javascript:void(0)"
   @click="$store.layout.toggleDarkMode()"
   class="flex items-center gap-2 px-3 py-2 rounded-md transition-all duration-200 
          hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm"
>
  <i :data-lucide="$store.layout.darkMode ? 'sun' : 'moon'" class="w-5 h-5"></i>
  <span x-show="!$store.layout.sidebarCollapsed" x-transition>{{ $label }}</span>
</a>
