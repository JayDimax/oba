<!DOCTYPE html>
<html lang="en" x-data x-init="$store.layout.init()" :class="{ 'dark': $store.layout.darkMode }">
<!-- Manifest and icon -->
<link rel="manifest" href="/manifest.json">
<link rel="icon" type="image/png" sizes="512x512" href="/icon-512.png">
<!-- FullCalendar CSS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
<meta name="theme-color" content="#1a202c">

<head>
  <script>
  // Immediately check for dark mode preference or saved state
  (function() {
    try {
      // Check if user previously saved preference in localStorage
      var darkMode = localStorage.getItem('darkMode');
      if (darkMode === 'true' || 
          (!darkMode && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
      } else {
        document.documentElement.classList.remove('dark');
      }
    } catch(e) {
      // Fail silently
    }
  })();
</script>

    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin Panel - <?php echo $__env->yieldContent('title', 'Dashboard'); ?></title>

    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <script src="https://unpkg.com/lucide@latest" defer></script>
    <script>document.addEventListener("DOMContentLoaded", () => lucide.createIcons());</script>
    <style>
      @media print {
        /* Hide buttons, navbars, footers during printing */
        button, .no-print, nav, .sidebar, .pagination {
          display: none !important;
        }
        /* Make the report table fill width */
        table {
          width: 100% !important;
          border-collapse: collapse !important;
        }
        table th, table td {
          border: 1px solid #000 !important;
          padding: 8px !important;
          color: #000 !important;
        }
        /* Optional: adjust font size for print */
        body {
          font-size: 12pt !important;
          color: #000 !important;
        }
      }
      [x-cloak] { display: none !important; }
      html, body { overflow-x: hidden; }

      aside.w-20 nav a span {
        display: none;
      }
      aside .flex.items-center svg {
        margin-right: 0.75rem;
      }
    </style>
    
</head>
<body class="bg-gray-100 text-gray-800 dark:text-gray-200 transition duration-300">

<div x-data="{ sidebarOpen: true }" class="flex min-h-screen">

    <!-- Sidebar -->
    <?php echo $__env->make('components.admin-sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col">

        <!-- Top Bar -->
        <header class="flex justify-end items-center space-x-4 p-4 border-b bg-white dark:bg-gray-800 dark:border-gray-700">
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="flex items-center space-x-2 focus:outline-none">
                    <img src="https://ui-avatars.com/api/?name=<?php echo e(urlencode(auth()->user()->name)); ?>"
                        alt="Avatar" class="w-8 h-8 rounded-full">
                    <span class="text-gray-700 dark:text-gray-200 font-medium"><?php echo e(auth()->user()->name); ?></span>
                    <i data-feather="chevron-down" class="w-4 h-4 text-gray-600 dark:text-gray-300"></i>
                </button>

                <!-- Dropdown -->
                <div x-show="open" @click.away="open = false"
                    class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-700 border dark:border-gray-600 rounded shadow z-50"
                    x-transition>
                    <a href="<?php echo e(route('profile.admin-edit')); ?>"
                    class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">
                        <i data-feather="user" class="mr-2 w-4 h-4 inline-block text-gray-600 dark:text-gray-300"></i> Profile
                    </a>
                    <form method="POST" action="<?php echo e(route('logout')); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit"
                                class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">
                            <i data-feather="log-out" class="mr-2 w-4 h-4 inline-block text-gray-600 dark:text-gray-300"></i> Logout
                        </button>
                    </form>
                </div>
            </div>

            <!-- Dark Mode Toggle Button -->
            <?php if (isset($component)) { $__componentOriginal2090438866f3dcdb76cd8b070bcc302d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2090438866f3dcdb76cd8b070bcc302d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.theme-toggle','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('theme-toggle'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2090438866f3dcdb76cd8b070bcc302d)): ?>
<?php $attributes = $__attributesOriginal2090438866f3dcdb76cd8b070bcc302d; ?>
<?php unset($__attributesOriginal2090438866f3dcdb76cd8b070bcc302d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2090438866f3dcdb76cd8b070bcc302d)): ?>
<?php $component = $__componentOriginal2090438866f3dcdb76cd8b070bcc302d; ?>
<?php unset($__componentOriginal2090438866f3dcdb76cd8b070bcc302d); ?>
<?php endif; ?>
        </header>

        <!-- Content -->
        <main class="p-6 flex-1 overflow-y-auto bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-100 transition-colors duration-300">
            <?php if(session('success')): ?>
            <script>
            document.addEventListener('alpine:init', () => {
                showToast(<?php echo json_encode(session('success'), 15, 512) ?>);
            });
            </script>
            <?php endif; ?>



            <?php echo $__env->yieldContent('content'); ?>
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

<script>
    document.addEventListener('DOMContentLoaded', () => {
        feather.replace();
        document.addEventListener('click', () => {
            setTimeout(() => feather.replace(), 50);
        });
    });
</script>

<script>
    function darkModeToggle() {
        return {
            isDark: false,
            init() {
                this.isDark = localStorage.getItem('darkMode') === 'true' || window.matchMedia('(prefers-color-scheme: dark)').matches;
            },
            toggleDark() {
                this.isDark = !this.isDark;
                localStorage.setItem('darkMode', this.isDark);
            }
        }
    }
</script>
<!-- FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
</body>
</html>
<?php /**PATH /home/u355685815/domains/orcasbettingapp.com/resources/views/layouts/admin.blade.php ENDPATH**/ ?>