<aside
  x-cloak
  :class="{
    'translate-x-0': sidebarOpen || window.innerWidth >= 640,
    '-translate-x-full': !sidebarOpen && window.innerWidth < 640,
    'w-16 sm:w-20': sidebarCollapsed,
    'w-72': !sidebarCollapsed
  }"
  class="sticky bottom-0 left-0 transform
         bg-white dark:bg-gray-800 shadow-md
         transition-all duration-300 ease-in-out flex flex-col w-72 items-center"
>
<!-- Sidebar content (left unchanged) -->
</aside>

<?php if (isset($component)) { $__componentOriginalbd26f90b0e8ad6cc54a49c99a73eac08 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbd26f90b0e8ad6cc54a49c99a73eac08 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.panel','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('layouts.panel'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
       <?php $__env->slot('sidebar', null, []); ?> 
            <?php echo $__env->make('partials.agent-sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
         <?php $__env->endSlot(); ?>
  
    <h1 class="text-2xl font-bold mb-4 text-gray-800 dark:text-white">🏆 Winning Bets</h1>
    

    
    <div class="flex justify-center mb-1">
        <form method="GET" action="<?php echo e(route('agent.winning')); ?>" class="flex items-center space-x-4" id="winfilter">
            <input
                type="date"
                id="draw_date"
                name="draw_date"
                value="<?php echo e(old('draw_date', $drawDate)); ?>"
                class="border px-3 py-2 rounded dark:bg-gray-700 dark:text-white dark:border-gray-600"
                max="<?php echo e(date('Y-m-d')); ?>"
                onchange="document.getElementById('winfilter').submit()" 
                required
            />
        </form>
    </div>

    <!-- UNCLAIMED WINNINGS -->
    <h3 class="text-lg font-bold text-gray-800 dark:text-white mt-6 mb-2">🎫 Unclaimed Winnings</h3>

    <?php $__empty_1 = true; $__currentLoopData = $unclaimed; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 mb-4">
        <div class="flex justify-between text-sm text-gray-500 dark:text-gray-300 mb-2">
        <span><?php echo e($bet->game_type); ?></span>
        <span><?php echo e(\Carbon\Carbon::parse($bet->created_at)->format('M d, Y h:i A')); ?></span>
        </div>

        <div class="text-sm space-y-2">
        <div class="flex justify-between">
            <span class="text-gray-500">Stub ID</span>
            <span class="text-gray-900 dark:text-white"><?php echo e($bet->stub_id ?? ($bet->stub->stub_id ?? 'N/A')); ?></span>
        </div>
        <div class="flex justify-between">
            <span class="text-gray-500">Amount</span>
            <span class="text-gray-900 dark:text-white">₱<?php echo e(number_format($bet->amount, 2)); ?></span>
        </div>
        <div class="flex justify-between">
            <span class="text-gray-500">Multiplier</span>
            <span class="text-gray-900 dark:text-white"><?php echo e($bet->display_multiplier); ?></span>
        </div>

        <div class="flex justify-between">
            <span class="text-gray-500">Total Winnings</span>
            <span class="text-yellow-600 dark:text-yellow-400 font-semibold">
            ₱<?php echo e(number_format($bet->winnings, 2)); ?>

            </span>

        </div>

        <div class="flex justify-between">
            <span class="text-gray-500">Status</span>
            <span class="text-yellow-600 text-xs font-bold">UNCLAIMED</span>
        </div>
        </div>

        <div class="mt-3 text-right">
        <a href="<?php echo e(route('agent.receipts.show', $bet->stub_id)); ?>" target="_blank" class="text-blue-600 text-sm hover:underline">Reprint</a>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <p class="text-center text-gray-500 dark:text-gray-400 mt-6">No unclaimed winnings for this date.</p>
    <?php endif; ?>


    <!-- CLAIMED WINNINGS -->
    <h3 class="text-lg font-bold text-gray-800 dark:text-white mt-8 mb-2">✅ Claimed Winnings</h3>

    <?php $__empty_1 = true; $__currentLoopData = $claimed; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 mb-4">
        <div class="flex justify-between text-sm text-gray-500 dark:text-gray-300 mb-2">
        <span><?php echo e($bet->game_type); ?></span>
        <span><?php echo e(\Carbon\Carbon::parse($bet->claim->claimed_at)->format('M d, Y h:i A')); ?></span>
        </div>

        <div class="text-sm space-y-2">
        <div class="flex justify-between">
            <span class="text-gray-500">Stub ID</span>
            <span class="text-gray-900 dark:text-white"><?php echo e($bet->stub_id ?? ($bet->stub->stub_id ?? 'N/A')); ?></span>
        </div>
        <div class="flex justify-between">
            <span class="text-gray-500">Amount</span>
            <span class="text-gray-900 dark:text-white">₱<?php echo e(number_format($bet->amount, 2)); ?></span>
        </div>
        <div class="flex justify-between">
            <span class="text-gray-500">Multiplier</span>
            <span class="text-gray-900 dark:text-white"><?php echo e($bet->multiplier); ?></span>
        </div>
        <div class="flex justify-between">
            <span class="text-gray-500">Total Winnings</span>
            <span class="text-yellow-600 dark:text-yellow-400 font-semibold">₱<?php echo e(number_format($bet->amount * $bet->multiplier, 2)); ?></span>
        </div>
        <div class="flex justify-between">
            <span class="text-gray-500">Status</span>
            <span class="text-green-600 text-xs font-bold">CLAIMED</span>
        </div>
        </div>

        <div class="mt-3 text-right">
        <a href="<?php echo e(route('agent.receipts.show', $bet->stub_id)); ?>" target="_blank" class="text-blue-600 text-sm hover:underline">Reprint</a>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <p class="text-center text-gray-500 dark:text-gray-400 mt-6">No claimed winnings for this date.</p>
    <?php endif; ?>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbd26f90b0e8ad6cc54a49c99a73eac08)): ?>
<?php $attributes = $__attributesOriginalbd26f90b0e8ad6cc54a49c99a73eac08; ?>
<?php unset($__attributesOriginalbd26f90b0e8ad6cc54a49c99a73eac08); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbd26f90b0e8ad6cc54a49c99a73eac08)): ?>
<?php $component = $__componentOriginalbd26f90b0e8ad6cc54a49c99a73eac08; ?>
<?php unset($__componentOriginalbd26f90b0e8ad6cc54a49c99a73eac08); ?>
<?php endif; ?>
<?php /**PATH D:\laragon\www\oba\resources\views/agent/winning-bets.blade.php ENDPATH**/ ?>