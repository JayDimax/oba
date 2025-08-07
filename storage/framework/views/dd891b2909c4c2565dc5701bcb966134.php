<!DOCTYPE html>
<html lang="en" x-data x-init="$store.layout.init()" :class="{ 'dark': $store.layout.darkMode }">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
  <title><?php echo e($title ?? config('app.name')); ?></title>

  <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>

  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  <script src="https://unpkg.com/lucide@latest" defer></script>
  <script>
    document.addEventListener("DOMContentLoaded", () => lucide.createIcons());
  </script>

  <style>
    [x-cloak] { display: none !important; }
    html, body { overflow-x: hidden; }
    @media (max-width: 639px) {
      .sidebar-mobile-offset { top: 3rem; }
    }
  </style>
</head>
<body class="bg-white dark:bg-gray-900 text-gray-900 dark:text-white font-sans">

  <!-- Overlay when mobile sidebar is open -->
  <div x-show="sidebarOpen && windowWidth < 640"
       @click="sidebarOpen = false"
       class="fixed inset-0 z-30 bg-black bg-opacity-50 sm:hidden"
       x-transition.opacity
       x-cloak>
  </div>

  <div class="flex min-h-screen" >
    
    <?php echo e($sidebar); ?>


    
    <main
      x-cloak
      :class="{
        'sm:ml-16 sm:ml-20': sidebarCollapsed && windowWidth >= 640,
        'sm:ml-72': !sidebarCollapsed && windowWidth >= 640
      }"
      class="flex-1 transition-all duration-300 p-4 sm:p-6 bg-white dark:bg-gray-900 text-gray-900 dark:text-white"
    >
      <?php echo e($slot); ?>

    </main>
  </div>

  <?php echo $__env->make('partials.betting-enhanced', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
  <?php echo $__env->make('partials.bet-modal', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
  <?php echo $__env->make('partials.toggle', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
  <?php echo $__env->make('partials.bet-modal-script', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
  
  <?php echo $__env->make('components.alert-toast', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</body>
</html>
<?php /**PATH /home/u355685815/domains/orcasbettingapp.com/resources/views/components/layouts/panel.blade.php ENDPATH**/ ?>