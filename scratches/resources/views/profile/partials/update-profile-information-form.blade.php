<x-app-layout>
    <section class="min-h-screen bg-gray-100 dark:bg-gray-900 py-6 px-4">
        <!-- Back Icon -->
        <div class="flex justify-end mb-4">
            <a href="{{ route('agent.profile') }}" class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-700 dark:text-white" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </a>
        </div>
    </section>

</x-app-layout>