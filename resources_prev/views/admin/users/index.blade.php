@extends('layouts.admin')
@section('title', 'Users')

@section('content')
<div x-data="{ showCreateModal: false, showEditModal: false, formData: {} }">

    <!-- Add User button -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">User Management</h2>
        <button @click="showCreateModal = true"
                class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-md font-semibold flex items-center gap-x-2">
            <i data-lucide="plus" class="w-5 h-5"></i>
            <span>Add User</span>
        </button>
    </div>

    <!-- Users Table -->
    <div class="overflow-x-auto bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700">
        <table class="w-full table-fixed text-sm text-left rounded overflow-hidden">
            <thead class="bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 uppercase text-xs border-b dark:border-gray-600">
                <tr>
                    <th class="px-4 py-3">#</th>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Agent Code</th>
                    <th class="px-4 py-3">Role</th>
                    <th class="px-4 py-3">Assigned Cashier</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Created</th>
                    <th class="px-4 py-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 border-b dark:border-gray-600">
                        <td class="px-4 py-2 text-center text-gray-700 dark:text-gray-300">{{ $loop->iteration }}</td>
                        <td class="px-4 py-2 text-gray-800 dark:text-gray-200">{{ $user->name }}</td>
                        <td class="px-4 py-2 font-mono text-sm text-red-600">{{ $user->agent_code }}</td>
                        <td class="px-4 py-2 capitalize text-gray-700 dark:text-gray-300">{{ $user->role }}</td>
                        <td class="px-4 py-2">
                            <div x-data="assignCashier({{ $user->id }}, '{{ $user->cashier_id ?? '' }}')">
                                <select x-model="selectedCashier"
                                        @change="updateCashier"
                                        class="w-full border px-2 py-1 rounded text-sm dark:bg-gray-800 dark:text-white">
                                    <option value="">-- Unassigned --</option>
                                    @foreach ($cashiers as $cashier)
                                        <option value="{{ $cashier->id }}">{{ $cashier->name }}</option>
                                    @endforeach
                                </select>

                                <template x-if="loading">
                                    <svg class="w-4 h-4 ml-2 text-gray-500 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                </template>
                            </div>
                        </td>

                        <td class="px-4 py-2">
                            @if ($user->is_active)
                                <span class="inline-block px-3 py-1 text-sm bg-green-100 dark:bg-green-800 text-green-800 dark:text-green-100 rounded-full">Active</span>
                            @else
                                <span class="inline-block px-3 py-1 text-sm bg-red-100 dark:bg-red-800 text-red-800 dark:text-red-100 rounded-full">Inactive</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-gray-500 dark:text-gray-400">{{ $user->created_at->format('F j, Y') }}</td>
                        <td class="px-4 py-2 text-center">
                            <div class="flex justify-center items-center gap-4">
                                {{-- <a href="#"
                                   @click.prevent="formData = {{ $user->toJson() }}; showEditModal = true"
                                   class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 text-sm font-medium">
                                    Edit
                                </a>
                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-800 text-sm font-medium">Del</button>
                                </form> --}}
                                <form method="POST" action="{{ route('admin.users.toggleActive', $user->id) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit"
                                            class="text-sm font-medium px-3 py-1 rounded transition
                                            {{ $user->is_active 
                                                ? 'bg-red-600 hover:bg-red-700 text-white' 
                                                : 'bg-green-500 hover:bg-green-600 text-white' }}">
                                        {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">No users found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="mt-4 px-4 py-2 dark:text-gray-300">
            {{ $users->links() }}
        </div>
    </div>

    <!-- ✅ Place MODALS inside Alpine scope -->
    @include('admin.users.partials.edit-modal')
    @include('admin.users.partials.create-modal')

</div> <!-- END Alpine wrapper -->

<!-- Alpine Store Toast -->
<div 
    x-data="{ show: false, message: '' }" 
    x-show="show" 
    x-transition 
    x-transition.duration.300ms
    x-transition.origin.top.right
    style="position: fixed; top: 1rem; right: 1rem; z-index: 9999;"
    class="bg-green-600 text-white px-4 py-2 rounded shadow-lg"
    x-text="message"
    @toast.window="message = $event.detail; show = true; setTimeout(() => show = false, 3000)"
></div>

@include('components.alert-toast')

<!-- Alpine Cashier Assign Script -->
<script>
function assignCashier(agentId, currentCashierId) {
    return {
        selectedCashier: currentCashierId,
        loading: false,

        async updateCashier() {
            this.loading = true;

            try {
                const response = await fetch(`/admin/agents/${agentId}/assign-cashier`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({
                        cashier_id: this.selectedCashier
                    })
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Failed to assign cashier.');
                }

                // Your toast system or fallback alert
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: data.message
                }));
            } catch (error) {
                alert(error.message || 'Assignment failed.');
                console.error(error);
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>

@endsection
