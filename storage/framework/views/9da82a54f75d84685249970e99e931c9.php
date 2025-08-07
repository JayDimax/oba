

<?php $__env->startSection('title', 'Remittance'); ?>

<?php $__env->startSection('content'); ?>
<h2 class="text-xl font-bold mb-4 text-gray-800 dark:text-gray-100">Receipts Remittances</h2>

<!-- Filter for Agent Remittance -->
<div class="mb-4 flex flex-wrap gap-4">
    <form method="GET" action="<?php echo e(route('cashier.remittance')); ?>" class="flex gap-2 items-end text-sm">
        <div>
            <label for="agent_id" class="block font-medium text-gray-700 dark:text-gray-300">Agent</label>
            <select name="agent_id" id="agent_id" class="border px-3 py-1 rounded dark:bg-gray-800 dark:text-white dark:border-gray-600">
                <option value="">All</option>
                <?php $__currentLoopData = $assignedAgents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $agent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($agent->id); ?>" <?php echo e(request('agent_id') == $agent->id ? 'selected' : ''); ?>>
                        <?php echo e($agent->name); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div>
            <label for="start_date" class="block font-medium text-gray-700 dark:text-gray-300">From</label>
            <input type="date" name="start_date" value="<?php echo e(request('start_date')); ?>" class="border px-3 py-1 rounded dark:bg-gray-800 dark:text-white dark:border-gray-600">
        </div>
        <div>
            <label for="end_date" class="block font-medium text-gray-700 dark:text-gray-300">To</label>
            <input type="date" name="end_date" value="<?php echo e(request('end_date')); ?>" class="border px-3 py-1 rounded dark:bg-gray-800 dark:text-white dark:border-gray-600">
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-1.5 rounded hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600">
            Filter
        </button>
    </form>
</div>


    
    <!-- Success Notification -->
    <div x-show="showSuccess"
        x-transition
        class="mb-4 px-4 py-2 bg-green-100 text-green-800 rounded text-sm font-medium">
        ✅ Payment successfully saved!
    </div>



<?php echo $__env->make('partials.pos', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<!-- Remittance Batches Table with matching style -->
    <?php $__currentLoopData = $collections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $collection): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="mb-8 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg shadow-sm">
        <div class="flex justify-between items-center mb-3">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white"><?php echo e($collection->agent->name); ?></h3>
        <p class="text-sm text-gray-600 dark:text-gray-400"><?php echo e(\Carbon\Carbon::parse($collection->collection_date)->format('F j, Y')); ?></p>
        </div>

        <div class="space-y-4">
        <?php $__currentLoopData = $collection->collectionStubs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
            $totalAmount = $stub->bets->sum('amount');
            $totalBets = $stub->bets->count();
            $latest = $stub->bets->max('created_at');
            ?>

            <div class="p-4 bg-white dark:bg-gray-900 rounded border border-gray-200 dark:border-gray-700 shadow-sm flex flex-col sm:flex-row sm:justify-between sm:items-center">
            <div class="mb-2 sm:mb-0">
                <p class="font-medium text-gray-800 dark:text-gray-200">Stub ID: <span class="font-normal"><?php echo e($stub->stub_id); ?></span></p>
                <p class="text-sm text-gray-600 dark:text-gray-400">Latest Bet: <?php echo e(\Carbon\Carbon::parse($latest)->format('M d, Y h:i A')); ?></p>
            </div>
            <div class="text-right sm:text-left">
                
                <p class="text-gray-700 dark:text-gray-300">Amount: <span class="font-semibold">₱<?php echo e(number_format($totalAmount, 2)); ?></span></p>
            </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <div class="mt-4">
    <?php echo e($collections->links()); ?>

</div>
  

<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.cashier', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/u355685815/domains/orcasbettingapp.com/resources/views/cashier/remittances.blade.php ENDPATH**/ ?>