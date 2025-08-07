<x-layouts.cashier-sidebar>
  <h1 class="text-3xl font-bold mb-6">Profile</h1>

  <div class="bg-gray-100 dark:bg-gray-800 p-6 rounded shadow max-w-md">
    <p><strong>Name:</strong> {{ auth()->user()->name }}</p>
    <p><strong>Agent Code:</strong> {{ auth()->user()->cashier_code }}</p>
    <p><strong>Phone:</strong> {{ auth()->user()->cashier?->phone ?? 'N/A' }}</p>
    <p><strong>Email:</strong> {{ auth()->user()->email }}</p>
  </div>
</x-layouts.cashier-sidebar>
