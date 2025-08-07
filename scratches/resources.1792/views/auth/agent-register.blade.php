<x-guest-layout>
    <x-auth-card>
        <x-slot name="logo">
            <h2 class="text-xl font-bold">Agent Registration</h2>
        </x-slot>

        <form method="POST" action="{{ route('agent.register.store') }}">
            @csrf

            <!-- Name -->
            <div>
                <label>Name</label>
                <input type="text" name="name" class="block w-full mt-1" required autofocus>
            </div>

            <!-- Password -->
            <div class="mt-4">
                <label>Password</label>
                <input type="password" name="password" class="block w-full mt-1" required>
            </div>

            <!-- Confirm Password -->
            <div class="mt-4">
                <label>Confirm Password</label>
                <input type="password" name="password_confirmation" class="block w-full mt-1" required>
            </div>

            <div class="mt-4">
                <button type="submit" class="w-full py-2 bg-blue-600 text-white rounded">
                    Register as Agent
                </button>
            </div>
        </form>
    </x-auth-card>
</x-guest-layout>
