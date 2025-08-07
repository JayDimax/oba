@extends('layouts.admin')

@section('content')
<div class="max-w-3xl mx-auto mt-10 bg-white dark:bg-gray-800 shadow-lg rounded-xl p-6">

    <h2 class="text-xl font-bold mb-6 text-center text-gray-800 dark:text-white">
        Edit Declared Draw Result
    </h2>

    @if(session('success'))
        <div class="mb-4 text-green-600 dark:text-green-400 font-semibold">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('admin.results.update', $result->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Game Date -->
            <div> 
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Game Date
                </label>
                <p class="text-gray-900 dark:text-gray-100 font-semibold">
                    {{ \Carbon\Carbon::parse($result->game_date)->toFormattedDateString() }}
                </p>
            </div>

            <!-- Game Type -->
            <div>
                <label for="game_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Game Type
                </label>
                <select id="game_type" name="game_type"
                        class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded focus:outline-none focus:ring focus:ring-blue-500"
                        required>
                    <option value="L2" {{ $result->game_type === 'L2' ? 'selected' : '' }}>Last 2</option>
                    <option value="S3" {{ $result->game_type === 'S3' ? 'selected' : '' }}>Swer 3</option>
                    <option value="4D" {{ $result->game_type === '4D' ? 'selected' : '' }}>4 Digits</option>
                </select>
            </div>
 
            <!-- Draw Time -->
            <div>
                <label for="game_draw" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Draw Time
                </label>
                <select id="game_draw" name="game_draw"
                        class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded focus:outline-none focus:ring focus:ring-blue-500"
                        required>
                    <option value="14:00" {{ $result->game_draw === '14:00' ? 'selected' : '' }}>2 PM</option>
                    <option value="17:00" {{ $result->game_draw === '17:00' ? 'selected' : '' }}>5 PM</option>
                    <option value="21:00" {{ $result->game_draw === '21:00' ? 'selected' : '' }}>9 PM</option>
                </select>
            </div>

            <!-- Winning Combination -->
            <div>
                <label for="winning_combination" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Winning Combination
                </label>
                <input
                    type="text"
                    id="winning_combination"
                    name="winning_combination"
                    value="{{ old('winning_combination', $result->winning_combination) }}"
                    class="w-full border border-gray-300 dark:border-gray-600 dark:bg-blue-700 text-white px-3 py-2 rounded text-center font-semibold tracking-widest text-lg"
                    required
                >
            </div>
        </div>

        <div class="flex justify-center mt-6">
            <button type="submit"
                class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded transition">
                Update Result
            </button>
        </div>
    </form>
</div>
@endsection
