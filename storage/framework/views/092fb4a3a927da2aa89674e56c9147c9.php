<aside
    x-cloak
    :class="{
        'translate-x-0': sidebarOpen || window.innerWidth >= 640,
        '-translate-x-full': !sidebarOpen && window.innerWidth < 640,
        'w-16 sm:w-20': sidebarCollapsed,
        'w-72': !sidebarCollapsed
    }"
    class="sticky bottom-0 left-0 transform bg-white dark:bg-gray-800 shadow-md transition-all duration-300 ease-in-out flex flex-col items-center"
>
    <!-- Sidebar content -->
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

    <!-- Page Title -->
    <h1 class="text-2xl font-bold mb-4 text-gray-800 dark:text-white">📁 Results</h1>

    <div class="min-h-screen dark:bg-gray-900 p-4 flex flex-col space-y-6">

        <div class="max-w-md mx-auto p-4" x-data="{ tab: '<?php echo e($draw ?? 'all'); ?>' }">

            <!-- Date Picker -->
            <form method="GET" action="<?php echo e(route('agent.results')); ?>" class="mb-4 flex justify-center">
                <input
                    type="date"
                    name="draw_date"
                    value="<?php echo e($date); ?>"
                    max="<?php echo e(date('Y-m-d')); ?>"
                    class="mt-1 block w-40 border border-gray-300 dark:border-gray-600 rounded px-3 py-1 
                           bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                    onchange="this.form.submit()"
                />
                <input type="hidden" name="draw" :value="tab" />
            </form>

            <!-- Tabs -->
            <div class="flex justify-center mb-4">
                <div class="grid grid-cols-4 w-full max-w-md bg-gray-200 dark:bg-gray-700 rounded-md overflow-hidden">
                    <?php $__currentLoopData = ['all' => 'All', '1st' => '1st Draw', '2nd' => '2nd Draw', '3rd' => '3rd Draw']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $active = ($draw === $key || ($key === 'all' && $draw === null));
                        ?>
                        <a 
                            href="<?php echo e(route('agent.results', ['draw_date' => $date, 'draw' => $key === 'all' ? null : $key])); ?>"
                            class="text-center px-2 py-2 text-sm font-medium transition-colors duration-200 
                                <?php echo e($active 
                                    ? 'bg-blue-600 text-white' 
                                    : 'text-gray-700 dark:text-white hover:bg-gray-300 dark:hover:bg-gray-600'); ?>"
                        >
                            <?php echo e($label); ?>

                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <!-- Result Cards -->
           <?php
                $filteredResults = collect($results);

                if ($draw === '1st') {
                    $filteredResults = $filteredResults->where('game_draw', '14');
                } elseif ($draw === '2nd') {
                    $filteredResults = $filteredResults->where('game_draw', '17');
                } elseif ($draw === '3rd') {
                    $filteredResults = $filteredResults->where('game_draw', '21');
                }
            ?>


            <?php $__empty_1 = true; $__currentLoopData = $filteredResults; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $result): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-3 mb-3">
                    <!-- Header: Game Type and Draw Time -->
                    <div class="flex justify-between text-sm text-gray-500 dark:text-gray-300 mb-1">
                        <?php
                            $drawTime = match($result->game_draw) {
                                '14' => '2PM',
                                '17' => '5PM',
                                '21' => '9PM',
                                default => $result->game_draw
                            };
                        ?>
                        <span><?php echo e($drawTime); ?> • <?php echo e(strtoupper($result->game_type)); ?></span>
                        <span><?php echo e(\Carbon\Carbon::parse($result->created_at)->format('h:i A')); ?></span>
                    </div>

                    <div class="flex justify-center gap-2 mt-2">
                        <?php $__currentLoopData = str_split($result->winning_combination); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $digit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-yellow-300 to-yellow-500 dark:from-yellow-500 dark:to-yellow-700 shadow-md flex items-center justify-center text-xl font-bold text-gray-900 dark:text-white">
                                <?php echo e($digit); ?>

                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-center text-gray-500 dark:text-gray-400 mt-10">No results found for this date.</p>
            <?php endif; ?>

        </div>
    </div>
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
<?php /**PATH D:\laragon\www\oba\resources\views/agent/results.blade.php ENDPATH**/ ?>