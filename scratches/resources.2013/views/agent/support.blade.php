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
</aside>

<x-layouts.panel>
    <x-slot name="sidebar">
      @include('partials.agent-sidebar')
    </x-slot>

  {{-- title header --}}
  <h1 class="text-2xl font-bold mb-4 text-gray-800 dark:text-white"> 🔔 Support</h1>

  {{-- page content --}}
    <div class="min-h-screen x-cloak dark:bg-gray-900 p-4 flex flex-col space-y-6">
        <p class="text-gray-600 dark:text-gray-300">If you need help, please contact our support team:</p>
          <ul class="list-disc ml-6 mt-4">
            {{-- <li>Email: jay.dimaculangan@holychild.edu.ph</li> --}}
            <li>Administrator</li>
            <li>For Any Concern: Please contact your administrator and provide screen shot for easy tracing of problem.</li>
            {{-- <li>Live Chat: Available 9AM - 6PM</li>
            <li>Messenger: Luke Lois</li>
            <li>Profile Pic: Backhoe with Vibro Hammer</li> --}}
        </ul>
    </div>
</x-layouts.panel>
