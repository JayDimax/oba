<!DOCTYPE html>
<html lang="en" x-data x-init="$store.layout.init()" :class="{ 'dark': $store.layout.darkMode }">
<!-- Manifest and icon -->
<link rel="manifest" href="/manifest.json">
<link rel="icon" type="image/png" sizes="512x512" href="/icon-512.png">
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
    <title>Cashier Panel - <?php echo $__env->yieldContent('title'); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>" />

    <?php echo app('Illuminate\Foundation\Vite')('resources/css/app.css'); ?>
    <script src="https://unpkg.com/lucide@latest" defer></script>
    <script>document.addEventListener("DOMContentLoaded", () => lucide.createIcons());</script>
    <!-- Alpine.js (only once!) -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Feather icons -->
    <script src="https://unpkg.com/feather-icons"></script>

    <!-- Lucide icons -->
    <script src="https://unpkg.com/lucide@latest" defer></script>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('layout', {
                darkMode: false,
                sidebarCollapsed: false,

                init() {
                    // Initialize dark mode from localStorage or system preference
                    const storedDark = localStorage.getItem('darkMode');
                    if (storedDark !== null) {
                        this.darkMode = storedDark === 'true';
                    } else {
                        this.darkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    }
                    this.applyDarkMode();

                    // Initialize sidebar collapse from localStorage
                    this.sidebarCollapsed = JSON.parse(localStorage.getItem('cashierSidebarCollapsed') || 'false');
                },

                toggleDarkMode() {
                    this.darkMode = !this.darkMode;
                    localStorage.setItem('darkMode', this.darkMode);
                    this.applyDarkMode();
                },

                applyDarkMode() {
                    if (this.darkMode) {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }
                },

                toggleSidebar() {
                    this.sidebarCollapsed = !this.sidebarCollapsed;
                    localStorage.setItem('cashierSidebarCollapsed', this.sidebarCollapsed);
                }
            });
        });
    </script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        html,
        body {
            overflow-x: hidden;
        }

        @media (max-width: 639px) {
            .sidebar-mobile-offset {
                top: 3rem;
            }
        }
    </style>
</head>

<body class="bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 transition duration-300">

    <div x-data="{ sidebarOpen: true }" class="flex min-h-screen">

        <!-- Sidebar -->
        
<aside x-data="{
  sidebarCollapsed: false,
  windowWidth: window.innerWidth,
  updateWidth() {
    this.windowWidth = window.innerWidth;
  },
  toggleSidebar() {
    this.sidebarCollapsed = !this.sidebarCollapsed;
    localStorage.setItem('cashierSidebarCollapsed', this.sidebarCollapsed);
  },
  init() {
    this.sidebarCollapsed = JSON.parse(localStorage.getItem('cashierSidebarCollapsed') || 'false');
    window.addEventListener('resize', () => this.updateWidth());
  }
}"
x-init="init()"
x-cloak
:class="{
  'w-16 sm:w-20': sidebarCollapsed,
  'w-64': !sidebarCollapsed
}"
class="bg-white dark:bg-gray-800 border-r dark:border-gray-700 transition-all duration-300 flex flex-col"
>

            <!-- Sidebar Toggle Button -->
            <div class="w-full flex justify-end px-3 py-2">
                <button @click="toggleSidebar" class="text-gray-600 dark:text-gray-300 hover:text-blue-600 dark:hover:text-white transition">
                <i :data-lucide="sidebarCollapsed ? 'menu' : 'menu'" class="w-5 h-5"></i>
                </button>
            </div>


            <!-- Profile Section -->
            <div x-show="!sidebarCollapsed" x-transition class="p-4 border-b dark:border-gray-700 text-center">
                <img src="https://ui-avatars.com/api/?name=<?php echo e(urlencode(Auth::user()->name)); ?>" alt="Avatar"
                    class="w-12 h-12 rounded-full mx-auto mb-2" />
                <h2 class="text-sm font-semibold truncate"><?php echo e(Auth::user()->name); ?></h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 truncate"><?php echo e(Auth::user()->agent_code ?? 'No Code'); ?></p>
                <p class="text-xs font-bold text-gray-500 dark:text-gray-400 truncate">CASHIER</p>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 mt-2 space-y-1">

                <a href="<?php echo e(route('cashier.dashboard')); ?>"
                    :class="{
                        'bg-blue-100 dark:bg-gray-700 text-blue-700 dark:text-blue-300 font-semibold':
                            $store.layout.sidebarCollapsed ? false : <?php echo e(request()->routeIs('cashier.dashboard') ? 'true' : 'false'); ?>,
                        'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200': true
                    }"
                    class="flex items-center px-4 py-2 transition">
                    <i data-feather="grid" class="w-5 h-5 mr-3"></i>
                    <span x-show="!sidebarCollapsed" x-transition>Dashboard</span>
                </a>

                <a href="<?php echo e(route('cashier.remittance')); ?>"
                    :class="{
                        'bg-blue-100 dark:bg-gray-700 text-blue-700 dark:text-blue-300 font-semibold':
                            <?php echo e(request()->routeIs('cashier.remittance') ? 'true' : 'false'); ?>,
                        'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200': true
                    }"
                    class="flex items-center px-4 py-2 transition">
                    <i data-feather="mail" class="w-5 h-5 mr-3"></i>
                    <span x-show="!sidebarCollapsed" x-transition>Remittance</span>
                </a>

                <a href="<?php echo e(route('cashier.pending')); ?>"
                    :class="{
                        'bg-blue-100 dark:bg-gray-700 text-blue-700 dark:text-blue-300 font-semibold':
                            <?php echo e(request()->routeIs('cashier.pending') ? 'true' : 'false'); ?>,
                        'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200': true
                    }"
                    class="flex items-center px-4 py-2 transition">
                    <i data-feather="send" class="w-5 h-5 mr-3"></i>
                    <span x-show="!sidebarCollapsed" x-transition>For Approval</span>
                </a>

                <a href="<?php echo e(route('cashier.receipts.index')); ?>"
                    :class="{
                        'bg-blue-100 dark:bg-gray-700 text-blue-700 dark:text-blue-300 font-semibold':
                            <?php echo e(request()->routeIs('cashier.receipts.index') ? 'true' : 'false'); ?>,
                        'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200': true
                    }"
                    class="flex items-center px-4 py-2 transition">
                    <i data-feather="file-text" class="w-5 h-5 mr-3"></i>
                    <span x-show="!sidebarCollapsed" x-transition>Receipts</span>
                </a>

                <a href="<?php echo e(route('cashier.reports')); ?>"
                    :class="{
                        'bg-blue-100 dark:bg-gray-700 text-blue-700 dark:text-blue-300 font-semibold':
                            <?php echo e(request()->routeIs('cashier.reports') ? 'true' : 'false'); ?>,
                        'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200': true
                    }"
                    class="flex items-center px-4 py-2 transition">
                    <i data-feather="bar-chart-2" class="w-5 h-5 mr-3"></i>
                    <span x-show="!sidebarCollapsed" x-transition>Reports</span>
                </a>
            </nav>

            <!-- Logout / Footer -->
            <div class="mt-auto p-4 border-t dark:border-gray-700">
                
                
                <p class="text-xs mt-4 text-center text-gray-400" x-show="!sidebarCollapsed">v1.0.0</p>
            </div>
        </aside>
 
        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col">

            <!-- Top Bar -->
            <header
                class="flex justify-end items-center space-x-4 p-4 border-b bg-white dark:bg-gray-800 dark:border-gray-700">
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="flex items-center space-x-2 focus:outline-none">
                        <img src="https://ui-avatars.com/api/?name=<?php echo e(urlencode(Auth::user()->name)); ?>" alt="Avatar"
                            class="w-8 h-8 rounded-full" />
                        <span class="text-gray-700 dark:text-gray-200 font-medium"><?php echo e(Auth::user()->name); ?></span>
                        <i data-feather="chevron-down" class="w-4 h-4 text-gray-600 dark:text-gray-300"></i>
                    </button>

                    <!-- Dropdown -->
                    <div x-show="open" @click.away="open = false"
                        class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-700 border dark:border-gray-600 rounded shadow z-50"
                        x-transition>
                        <a href="<?php echo e(route('profile.edit')); ?>" 
                            class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">
                            <i data-feather="user" class="mr-2 w-4 h-4 inline-block text-gray-600 dark:text-gray-300"></i> Profile
                        </a>

                        <form method="POST" action="<?php echo e(route('logout')); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit"
                                class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">
                                <i data-feather="log-out"
                                    class="mr-2 w-4 h-4 inline-block text-gray-600 dark:text-gray-300"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Optional Dark Mode Toggle Button in top bar -->
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

            <!-- Page Content -->
            <main
                class="bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-100 transition-colors duration-300 min-h-screen px-4 sm:px-6 lg:px-8 py-6">
                <div class="max-w-7xl mx-auto space-y-6">
                    <?php echo $__env->yieldContent('content'); ?>
                    <?php echo $__env->make('cash-partials.toggle', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                </div>
            </main>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            feather.replace();
            document.addEventListener('click', () => {
                setTimeout(() => feather.replace(), 50);
            });
        });
    </script>
</body>

</html>
<?php /**PATH /home/u355685815/domains/orcasbettingapp.com/resources/views/layouts/cashier.blade.php ENDPATH**/ ?>