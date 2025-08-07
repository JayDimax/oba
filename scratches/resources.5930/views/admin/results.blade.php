@extends('layouts.admin')
@section('title', 'Results')

@section('content')
<div x-data="{ tab: 'declare' }">

    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Result Management</h2>
    </div>

    <!-- Grid Layout -->
    <div class="grid grid-cols-12 gap-6">

        <!-- LEFT PANEL (4 columns): Declare Result Form -->
        <div class="col-span-4 space-y-4">
            <!-- Form Content -->
            <div class="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md p-4 shadow">
                <div x-show="tab === 'declare'" x-cloak>
                    @include('admin.results.partials.declare-form')
                </div>
            </div>
        </div>

        <!-- RIGHT PANEL (8 columns): Results Table -->
        <div class="col-span-8">
            
            <!-- Results Table -->
            <div class="overflow-x-auto bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700 mt-6 p-4">
                <form method="GET" action="{{ route('admin.results.index') }}" class="mb-4 flex items-end gap-4 flex-wrap">
                    <div>
                        <label for="filter_game_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Game Type</label>
                        <select name="game_type" id="filter_game_type" class="mt-1 block w-40 border border-gray-300 dark:border-gray-600 rounded px-3 py-1 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            <option value="">All</option>
                            <option value="L2" {{ request('game_type') == 'L2' ? 'selected' : '' }}>L2</option>
                            <option value="S3" {{ request('game_type') == 'S3' ? 'selected' : '' }}>S3</option>
                            <option value="4D" {{ request('game_type') == '4D' ? 'selected' : '' }}>4D</option>
                        </select>
                    </div>

                    <div>
                        <label for="filter_draw" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Draw Time</label>
                        <select name="game_draw" id="filter_draw" class="mt-1 block w-40 border border-gray-300 dark:border-gray-600 rounded px-3 py-1 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            <option value="">All</option>
                            <option value="14:00" {{ request('game_draw') == '14:00' ? 'selected' : '' }}>2 PM</option>
                            <option value="17:00" {{ request('game_draw') == '17:00' ? 'selected' : '' }}>5 PM</option>
                            <option value="21:00" {{ request('game_draw') == '21:00' ? 'selected' : '' }}>9 PM</option>
                        </select>
                    </div>

                    <div>
                        <label for="filter_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date</label>
                        <input
                            type="date"
                            name="game_date"
                            id="filter_date"
                            class="mt-1 block w-40 border border-gray-300 dark:border-gray-600 rounded px-3 py-1 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                            value="{{ request('game_date') }}"
                            max="{{ now()->toDateString() }}"
                        >
                    </div>

                    <div>
                        <label for="filter_combination" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Winning Combination</label>
                        <input
                            type="text"
                            name="winning_combination"
                            id="filter_combination"
                            class="mt-1 block w-40 border border-gray-300 dark:border-gray-600 rounded px-3 py-1 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                            placeholder="e.g. 12 or 123"
                            value="{{ request('winning_combination') }}"
                        >
                    </div>

                    <div class="pt-5 flex space-x-2">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm font-semibold transition">
                            Filter
                        </button>
                        <a href="{{ route('admin.results.index') }}" 
                           class="bg-gray-300 hover:bg-gray-400 text-gray-700 dark:bg-gray-600 dark:hover:bg-gray-500 dark:text-gray-300 px-4 py-2 rounded text-sm font-semibold transition">
                            Clear Filters
                        </a>
                    </div>
                </form>

                <table class="w-full table-fixed text-sm text-left border border-gray-200 dark:border-gray-700 rounded overflow-hidden">
                    <thead class="bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 uppercase text-xs border-b border-gray-300 dark:border-gray-600">
                        <tr>
                            <th class="px-4 py-3 w-[5%]">#</th>
                            <th class="px-4 py-3 w-[15%]">Game Type</th>
                            <th class="px-4 py-3 w-[15%]">Draw Time</th>
                            <th class="px-4 py-3 w-[20%]">Winning Comb.</th>
                            <th class="px-4 py-3 w-[10%]">Date</th>
                            <th class="px-4 py-3 w-[10%] text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($results as $result)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                                <td class="px-4 py-2 text-center text-gray-800 dark:text-gray-300">{{ $loop->iteration }}</td>
                                <td class="px-4 py-2 font-semibold text-gray-900 dark:text-gray-100">{{ $result->game_type }}</td>
                                <td class="px-4 py-2 text-gray-800 dark:text-gray-300">{{ $result->game_draw }}</td>
                                <td class="px-4 py-2 font-mono text-sm text-gray-900 dark:text-gray-100">{{ $result->winning_combination }}</td>
                                <td class="px-4 py-2 text-gray-800 dark:text-gray-300">{{ \Carbon\Carbon::parse($result->game_date)->format('M d, Y') }}</td>

                                <td class="px-4 py-2 text-center">
                                    <div class="flex justify-center items-center gap-3">
                                        <!-- Edit Button -->
                                        <a href="{{ route('admin.results.edit', $result->id) }}"
                                           class="inline-block text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-600 text-sm font-medium transition">
                                            Edit
                                        </a>

                                        <!-- Delete Button -->
                                        <form action="{{ route('admin.results.delete', $result->id) }}" method="POST"
                                              onsubmit="return confirm('Are you sure you want to delete this result?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-600 text-sm font-medium cursor-pointer bg-transparent border-none transition">
                                                Del
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">No results declared yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $results->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@include('components.alert-toast')
