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


<h1 class="text-2xl font-bold mb-4 text-gray-800 dark:text-white">🕘 Bet History</h2>
    
  <div class="min-h-screen x-cloak dark:bg-gray-900 p-4 flex flex-col space-y-6">
        <form method="GET" action="<?php echo e(route('agent.bet.history')); ?>" id="dateFilterForm" class="mb-3 text-center">
          <p class="text-xs text-gray-500 mb-1">You can only select dates within the last 3 days.</p>

         <input 
            type="date" 
            name="date" 
            value="<?php echo e($date->toDateString()); ?>" 
            min="<?php echo e($maxPastDate->toDateString()); ?>" 
            max="<?php echo e($today->toDateString()); ?>" 
            onchange="document.getElementById('dateFilterForm').submit()" 
            class="border rounded px-2 py-1 bg-white text-gray-900 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-600"
          />

        </form>
        <?php echo e($bets->links()); ?>

        <?php $__empty_1 = true; $__currentLoopData = $bets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
          <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-3">
            <div class="flex justify-between text-sm text-gray-500 dark:text-gray-300 mb-1">
              <span><?php echo e(formatDrawTime($bet->game_draw)); ?> • <?php echo e($bet->game_type); ?></span>
              <span><?php echo e(\Carbon\Carbon::parse($bet->created_at)->format('h:i:s A')); ?></span>
            </div>

            <?php $__currentLoopData = $bet->bets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <?php
                $multiplier = $item->multiplier ?? 1;
                $winnings = $item->is_winner ? $item->amount * $multiplier : 0;
              ?>
              <div class="flex justify-between items-center text-sm py-1">
                <div class="flex gap-1">
                  <?php $__currentLoopData = str_split($item->bet_number); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $digit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="w-6 h-6 rounded-full 
                                <?php echo e($item->is_winner ? 'bg-green-300 dark:bg-green-700 text-white font-bold' : 'bg-gray-200 dark:bg-gray-600'); ?> 
                                flex items-center justify-center text-sm">
                      <?php echo e($digit); ?>

                    </div>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <div class="text-right leading-tight">
                  <span class="text-gray-700 dark:text-gray-100 text-xs">(x<?php echo e($multiplier); ?>)</span><br>
                  ₱<?php echo e(number_format($item->amount, 2)); ?>


                  <?php if($item->is_winner): ?>
                    <div class="text-green-600 text-xs font-semibold">🏆 Winner</div>
                    <div class="text-xs text-green-600">+₱<?php echo e(number_format($winnings, 2)); ?></div>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <div class="flex justify-between mt-2 font-semibold text-sm">
              <span class="text-gray-500">TOTAL</span>
              <span class="text-gray-900 dark:text-white">₱<?php echo e(number_format($bet->total ?? 0, 2)); ?></span>
            </div>

            <?php if($bet->winnings !== null): ?>
              <p class="text-sm font-bold text-green-600 mt-1">Winnings: ₱<?php echo e(number_format($bet->winnings, 2)); ?></p>
            <?php elseif($bet->total !== null): ?>
              <p class="text-xs text-yellow-500 mt-1">Waiting for result or multiplier...</p>
            <?php endif; ?>

            <div class="text-xs text-gray-400 mt-1">Stub ID: <?php echo e($bet->stub_id); ?></div>

            <div class="mt-2 text-right">
              <a href="<?php echo e(route('agent.receipts.show', $bet->stub_id)); ?>" target="_blank" class="text-blue-600 text-sm hover:underline">Reprint</a>
            </div>
          </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
          <p class="text-center text-gray-500 dark:text-gray-400 mt-10">No bets found for this date.</p>
        <?php endif; ?>
          
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

<?php
  function formatDrawTime($drawCode) {
    return match ($drawCode) {
      '14' => '2PM',
      '17' => '5PM',
      '21' => '9PM',
      default => $drawCode,
    };
  }
?>
<?php /**PATH D:\laragon\www\oba\resources\views/agent/bet-history.blade.php ENDPATH**/ ?>