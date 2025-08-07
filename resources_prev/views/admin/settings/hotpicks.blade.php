@extends('layouts.admin')

@section('title', 'Hot Pick Settings')

@section('content')
<div x-data="{ showEditModal: false, formData: {} }">

    <div class="grid grid-cols-12 gap-6 mb-6">

        {{-- Left Column: Create/Update Form --}}
        <div class="col-span-12 md:col-span-4">
            <div class="bg-white dark:bg-gray-900 p-6 rounded shadow border dark:border-gray-700">
                <h2 class="text-lg font-bold mb-4 text-gray-800 dark:text-white">Set Limit per Game Type</h2>

                @if(session('success'))
                    <div class="mb-4 text-green-600 dark:text-green-400 font-semibold">{{ session('success') }}</div>
                @endif

                <form action="{{ route('admin.settings.hotpicks.update') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label for="game_type" class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">Game Type</label>
                        <select name="game_type" id="game_type" class="w-full border rounded px-3 py-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                            <option value="L2">Last 2 (L2)</option>
                            <option value="S3">Swer 3 (S3)</option>
                            <option value="4D">4 Digits (4D)</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="limit" class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">Limit Value</label>
                        <input type="number" name="limit" id="limit"
                               class="w-full border rounded px-3 py-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                    </div>

                    <button type="submit"
                            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition text-sm font-semibold">
                        Save Limit
                    </button>
                </form>
            </div>
        </div>

        {{-- Right Column: Limit Table --}}
        <div class="col-span-12 md:col-span-8">
            <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700 overflow-x-auto">
                <table class="w-full table-auto text-sm text-left">
                    <thead class="bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 uppercase text-xs border-b dark:border-gray-700">
                        <tr>
                            <th class="px-4 py-3">#</th>
                            <th class="px-4 py-3">Game Type</th>
                            <th class="px-4 py-3">Limit Bets</th>
                            <th class="px-4 py-3">Last Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($hotpicks as $index => $row)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 border-b dark:border-gray-700 text-gray-700 dark:text-gray-200">
                                <td class="px-4 py-2">{{ $index + 1 }}</td>
                                <td class="px-4 py-2 font-bold">{{ $row->game_type }}</td>
                                <td class="px-4 py-2 text-blue-600 dark:text-blue-400">{{ $row->limit }}</td>
                                <td class="px-4 py-2 text-gray-500 dark:text-gray-400">
                                    {{ \Carbon\Carbon::parse($row->updated_at)->format('F j, Y H:i') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-3 text-center text-gray-500 dark:text-gray-400">
                                    No limits configured yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-4 px-4 dark:text-white">
                    {{ $hotpicks->links() }}
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
