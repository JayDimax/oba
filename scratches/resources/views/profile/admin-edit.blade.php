@extends('layouts.admin')

@section('title', 'Change Password')

@section('content')
    <div class="max-w-xl mx-auto">
        <div class="bg-white dark:bg-gray-800 shadow rounded-xl p-6">

            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">
                Change Password
            </h2>

            {{-- Success Message --}}
            @if (session('status'))
                <div class="mb-4 p-3 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Password Update Form --}}
            <form method="POST" action="{{ route('profile.admin-update') }}" class="space-y-5">
                @csrf
                @method('PATCH')

                {{-- Current Password --}}
                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Current Password
                    </label>
                    <input type="password" name="current_password" id="current_password" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600">
                    @error('current_password')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- New Password --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        New Password
                    </label>
                    <input type="password" name="password" id="password" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600">
                    @error('password')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Confirm New Password --}}
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Confirm New Password
                    </label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600">
                </div>

                {{-- Submit Button --}}
                <div class="flex mt-2 text-center">
                    <button type="submit"
                        class="px-5 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 transition">
                        Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
