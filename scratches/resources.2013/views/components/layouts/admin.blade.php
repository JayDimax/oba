<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin Panel - @yield('title', 'Dashboard')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

</head>
<body class="bg-gray-100 text-gray-800">

<div class="min-h-screen flex">
    {{-- Sidebar --}}
    @include('components.admin-sidebar')

    {{-- Main Content Area --}}
    <div class="flex-1 flex flex-col">
        {{-- Header --}}
        <header class="bg-white shadow-md px-6 py-4 flex justify-between items-center">
            <h1 class="text-xl font-semibold">@yield('title', 'Dashboard')</h1>
            <div class="flex items-center gap-4">
                <span class="text-sm">Hello, {{ auth()->user()->name }}</span>
            </div>
        </header>

        {{-- Content --}}
        <main class="p-6 flex-1 overflow-y-auto">
            @if(session('success'))
            <script>
            document.addEventListener('alpine:init', () => {
                showToast(@json(session('success')));
            });
            </script>
            @endif

            @yield('content')
        </main>
    </div>
</div>
<div 
  x-data="toastHandler()" 
  x-show="visible" 
  x-transition 
  class="fixed bottom-6 right-6 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg flex items-center space-x-2"
  x-cloak
>
  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
  </svg>
  <span x-text="message"></span>
</div>

<script>
  function toastHandler() {
    return {
      visible: false,
      message: '',
      timeout: null,
      show(message) {
        this.message = message;
        this.visible = true;
        clearTimeout(this.timeout);
        this.timeout = setTimeout(() => this.visible = false, 3000);
      }
    }
  }

  function showToast(message) {
    const toast = document.querySelector('[x-data="toastHandler()"]')?.__x;
    if (toast) toast.show(message);
  }
</script>

</body>
</html>
