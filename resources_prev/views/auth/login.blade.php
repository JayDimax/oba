<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br px-4">
        <div class="w-full max-w-md bg-white/10 backdrop-blur-lg rounded-xl shadow-lg p-8 space-y-6 text-center border border-white/30">

            {{-- Logo --}}
            <div class="flex justify-center">
                <img src="{{ asset('images/orca-logo.png') }}" alt="Logo" class="h-20 w-auto">
            </div>

            <h1 class="text-4xl font-bold text-gray-800 dark:text-gray-100">ORCAS</h1>
            <p class="text-lg text-gray-600 dark:text-gray-300">Betting App</p>

            {{-- Session Status --}}
            @if (session('status'))
                <div class="text-green-600 dark:text-green-400 font-medium">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Login Form --}}
            <form method="POST" action="{{ route('login') }}" class="space-y-6 text-left">
                @csrf

                {{-- Agent Code --}}
                <div>
                    <label for="agent_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">User Code</label>
                    <input id="agent_code" name="agent_code" type="text" required autofocus
                        class="w-full mt-1 px-3 py-2 border border-gray-300 dark:border-gray-700 rounded bg-white/80 dark:bg-gray-900 text-gray-900 dark:text-gray-100 shadow-sm focus:ring focus:ring-blue-500" />
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password"
                        class="w-full mt-1 px-3 py-2 border border-gray-300 dark:border-gray-700 rounded bg-white/80 dark:bg-gray-900 text-gray-900 dark:text-gray-100 shadow-sm focus:ring focus:ring-blue-500" />
                    @error('password')
                        <p class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Remember Me --}}
                <div class="flex items-center">
                    <input id="remember_me" type="checkbox" name="remember"
                        class="mr-2 rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                    <label for="remember_me" class="text-sm text-gray-600 dark:text-gray-400">Remember me</label>
                </div>

                {{-- Actions --}}
                <div class="flex flex-col gap-3 mt-6">
                    <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-lg transition">
                        Log in
                    </button>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}"
                            class="text-sm text-center text-gray-600 dark:text-gray-400 hover:underline">
                            Forgot your password?
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
