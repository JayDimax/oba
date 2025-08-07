<aside
  x-cloak
  :class="{
    'translate-x-0': sidebarOpen || window.innerWidth >= 640,
    '-translate-x-full': !sidebarOpen && window.innerWidth < 640,
    'w-16 sm:w-20': sidebarCollapsed,
    'w-72': !sidebarCollapsed
  }"
  class="sticky bottom-0 left-0 transform
         bg-white dark:bg-gray-800 shadow-md
         transition-all duration-300 ease-in-out flex flex-col w-72 items-center"
>
<!-- Sidebar content (left unchanged) -->
</aside>

<x-layouts.panel>
      <x-slot name="sidebar">
    @include('partials.agent-sidebar')
  </x-slot>
    <h1 class="text-2xl font-bold mb-4 text-gray-800 dark:text-white">⚙️ Settings</h1>
    <div class="min-h-screen x-cloak dark:bg-gray-900 p-4 flex flex-col space-y-6">
        <p class="text-gray-600 dark:text-gray-300">This is where your settings will be shown.</p>
        
                  <ul class="list-disc ml-6 mt-4">
            <li>Email: jay.dimaculangan@holychild.edu.ph</li>
            <li>Phone: 0936-272-1861 / 0991-205-9748</li>
            <li>Live Chat: Available 9AM - 6PM</li>
            <li>Messenger: Luke Lois</li>
            <li>Profile Pic: Backhoe with Vibro Hammer</li>
        </ul><!-- Later we’ll add a dynamic table -->
    </div>
</x-layouts.panel>
