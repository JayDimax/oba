<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-sky-70 via-indigo-100 to-purple-200 px-4">

        {{-- Glass Card --}}
        <div class="w-full max-w-md bg-white/20 backdrop-blur-lg rounded-xl shadow-lg p-8 space-y-6 text-center border border-white/30">

            {{-- Centered Logo --}}
            <div class="flex justify-center">
                <img src="{{ asset('images/orca-logo.png') }}" alt="Logo" class="h-20 w-auto">
            </div>

            <h1 class="text-4xl font-bold text-gray-800 dark:text-gray-100">ORCAS</h1>
            <p class="text-lg text-gray-600 dark:text-gray-300">Betting App</p>

            <a href="{{ route('login') }}"
                class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-lg transition">
                Login
            </a>
        </div>

    </div>
</x-guest-layout>
