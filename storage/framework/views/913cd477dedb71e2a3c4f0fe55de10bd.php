<?php if (isset($component)) { $__componentOriginal69dc84650370d1d4dc1b42d016d7226b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal69dc84650370d1d4dc1b42d016d7226b = $attributes; } ?>
<?php $component = App\View\Components\GuestLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('guest-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(App\View\Components\GuestLayout::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-sky-70 via-indigo-100 to-purple-200 px-4">

        
        <div class="w-full max-w-md bg-white/20 backdrop-blur-lg rounded-xl shadow-lg p-8 space-y-6 text-center border border-white/30">

            
            <div class="flex justify-center">
                <img src="<?php echo e(asset('images/orca-logo.png')); ?>" alt="Logo" class="h-20 w-auto">
            </div>

            <h1 class="text-4xl font-bold text-gray-800 dark:text-gray-100">ORCAS</h1>
            <p class="text-lg text-gray-600 dark:text-gray-300">Betting App</p>

            <a href="<?php echo e(route('login')); ?>"
                class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-lg transition">
                Login
            </a>
        </div>

    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal69dc84650370d1d4dc1b42d016d7226b)): ?>
<?php $attributes = $__attributesOriginal69dc84650370d1d4dc1b42d016d7226b; ?>
<?php unset($__attributesOriginal69dc84650370d1d4dc1b42d016d7226b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal69dc84650370d1d4dc1b42d016d7226b)): ?>
<?php $component = $__componentOriginal69dc84650370d1d4dc1b42d016d7226b; ?>
<?php unset($__componentOriginal69dc84650370d1d4dc1b42d016d7226b); ?>
<?php endif; ?>
<?php /**PATH F:\laragon\www\oba\resources\views/welcome.blade.php ENDPATH**/ ?>