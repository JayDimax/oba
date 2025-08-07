

<?php $__env->startSection('title', 'Cashier Pending Approvals'); ?>

<?php $__env->startSection('content'); ?>
    <h1 class="text-xl font-bold mb-4">🧾 Pending Remittances</h1>

    <?php if(session('success')): ?>
        <div 
            x-data="{ show: true }" 
            x-init="setTimeout(() => show = false, 3000)" 
            x-show="show" 
            x-transition 
            class="mb-4 px-4 py-2 bg-green-100 text-green-800 rounded text-sm font-medium"
        >
            ✅ <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <ul class="space-y-4">
    <?php $__empty_1 = true; $__currentLoopData = $pendingAgents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $agent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <li class="p-4 bg-white dark:bg-gray-800 rounded shadow text-gray-800 dark:text-gray-200">
            <div class="flex justify-between items-center mb-2">
                <span class="font-medium text-lg"><?php echo e($agent->name); ?></span>
                <span class="text-red-500 font-semibold text-lg">₱<?php echo e(number_format($agent->unpaid_amount, 2)); ?></span>
            </div>

            <div class="text-sm text-gray-600 dark:text-gray-400 ml-1 mb-3">
                Gross: ₱<?php echo e(number_format($agent->gross ?? 0, 2)); ?> |
                Deductions: ₱<?php echo e(number_format($agent->deductions ?? 0, 2)); ?>

            </div>

            
            <?php
                $latestCollection = $agent->collections->where('status', 'pending')->sortByDesc('created_at')->first();
            ?>

            <?php if($latestCollection && $latestCollection->proof_file): ?>
                <div class="mt-2 mb-3">
                    <a href="<?php echo e(asset('storage/' . $latestCollection->proof_file)); ?>" target="_blank">
                        <img src="<?php echo e(asset('storage/' . $latestCollection->proof_file)); ?>" 
                             alt="Receipt Photo" 
                             class="h-32 w-auto border rounded shadow-md hover:scale-105 transition-all duration-200">
                    </a>
                </div>
            <?php else: ?>
                <p class="text-sm text-gray-400 italic">No receipt uploaded.</p>
            <?php endif; ?>

            <!-- Approve Button -->
            <div class="text-right">
                <form action="<?php echo e(route('cashier.approveAll', $agent->id)); ?>" method="POST" onsubmit="return confirm('Approve all pending remittances for <?php echo e($agent->name); ?>?');">
                    <?php echo csrf_field(); ?>
                    <button type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-1 rounded text-sm shadow-sm">
                        ✅ Approve All
                    </button>
                </form>
            </div>
        </li>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <li class="p-4 text-center text-gray-500 dark:text-gray-400">All remittances are settled.</li>
    <?php endif; ?>
</ul>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.cashier', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/u355685815/domains/orcasbettingapp.com/resources/views/cashier/pending-collections.blade.php ENDPATH**/ ?>