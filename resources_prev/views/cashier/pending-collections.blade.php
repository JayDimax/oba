@extends('layouts.cashier')

@section('title', 'Cashier Pending Approvals')

@section('content')
    <h1 class="text-xl font-bold mb-4">🧾 Pending Remittances</h1>

    @if (session('success'))
        <div 
            x-data="{ show: true }" 
            x-init="setTimeout(() => show = false, 3000)" 
            x-show="show" 
            x-transition 
            class="mb-4 px-4 py-2 bg-green-100 text-green-800 rounded text-sm font-medium"
        >
            ✅ {{ session('success') }}
        </div>
    @endif

    <ul class="space-y-4">
    @forelse($pendingAgents as $agent)
        <li class="p-4 bg-white dark:bg-gray-800 rounded shadow text-gray-800 dark:text-gray-200">
            <div class="flex justify-between items-center mb-2">
                <span class="font-medium text-lg">{{ $agent->name }}</span>
                <span class="text-red-500 font-semibold text-lg">₱{{ number_format($agent->unpaid_amount, 2) }}</span>
            </div>

            <div class="text-sm text-gray-600 dark:text-gray-400 ml-1 mb-3">
                Gross: ₱{{ number_format($agent->gross ?? 0, 2) }} |
                Deductions: ₱{{ number_format($agent->deductions ?? 0, 2) }}
            </div>

            {{-- Receipt Photo from Latest Collection --}}
            @php
                $latestCollection = $agent->collections->where('status', 'pending')->sortByDesc('created_at')->first();
            @endphp

            @if ($latestCollection && $latestCollection->proof_file)
                <div class="mt-2 mb-3">
                    <a href="{{ asset('storage/' . $latestCollection->proof_file) }}" target="_blank">
                        <img src="{{ asset('storage/' . $latestCollection->proof_file) }}" 
                             alt="Receipt Photo" 
                             class="h-32 w-auto border rounded shadow-md hover:scale-105 transition-all duration-200">
                    </a>
                </div>
            @else
                <p class="text-sm text-gray-400 italic">No receipt uploaded.</p>
            @endif

            <!-- Approve Button -->
            <div class="text-right">
                <form action="{{ route('cashier.approveAll', $agent->id) }}" method="POST" onsubmit="return confirm('Approve all pending remittances for {{ $agent->name }}?');">
                    @csrf
                    <button type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-1 rounded text-sm shadow-sm">
                        ✅ Approve All
                    </button>
                </form>
            </div>
        </li>
    @empty
        <li class="p-4 text-center text-gray-500 dark:text-gray-400">All remittances are settled.</li>
    @endforelse
</ul>

@endsection
