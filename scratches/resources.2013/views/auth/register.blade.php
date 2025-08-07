<x-guest-layout>
    <form method="POST" action="{{ route('register') }}" class="bg-white dark:bg-gray-900 p-6 rounded-lg shadow-md space-y-6">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" class="text-gray-700 dark:text-gray-200" />
            <x-text-input
                id="name"
                class="block mt-1 w-full dark:bg-gray-800 dark:border-gray-700 dark:text-white"
                type="text"
                name="name"
                :value="old('name')"
                required
                autofocus
                autocomplete="name"
            />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" class="text-gray-700 dark:text-gray-200" />
            <x-text-input
                id="email"
                class="block mt-1 w-full dark:bg-gray-800 dark:border-gray-700 dark:text-white"
                type="email"
                name="email"
                :value="old('email')"
                required
                autocomplete="username"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" class="text-gray-700 dark:text-gray-200" />
            <x-text-input
                id="password"
                class="block mt-1 w-full dark:bg-gray-800 dark:border-gray-700 dark:text-white"
                type="password"
                name="password"
                required
                autocomplete="new-password"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="text-gray-700 dark:text-gray-200" />
            <x-text-input
                id="password_confirmation"
                class="block mt-1 w-full dark:bg-gray-800 dark:border-gray-700 dark:text-white"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
            />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end mt-6">
            <a
                class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                href="{{ route('login') }}"
            >
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ml-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
