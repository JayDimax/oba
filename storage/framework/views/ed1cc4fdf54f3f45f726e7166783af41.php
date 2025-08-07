
<?php $__env->startSection('title', 'Settings'); ?>

<?php $__env->startSection('content'); ?>
<div class="p-6 bg-white dark:bg-gray-800 rounded shadow space-y-4">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-white mb-4">System Settings</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Multipliers -->
        <a href="<?php echo e(route('admin.settings.multipliers')); ?>" 
           class="block bg-blue-100 dark:bg-blue-900 hover:bg-blue-200 dark:hover:bg-blue-800 text-blue-800 dark:text-blue-300 p-4 rounded shadow transition">
            <h2 class="text-lg font-semibold mb-2">Multipliers</h2>
            <p class="text-sm">Set payout multipliers for each game type.</p>
        </a>

        <!-- Agent Commissions -->
        <a href="<?php echo e(route('admin.settings.commissions')); ?>" 
           class="block bg-green-100 dark:bg-green-900 hover:bg-green-200 dark:hover:bg-green-800 text-green-800 dark:text-green-300 p-4 rounded shadow transition">
            <h2 class="text-lg font-semibold mb-2">Agent Commissions</h2>
            <p class="text-sm">Assign commission percentages per agent and game type.</p>
        </a>

        <!-- Hot Picks -->
        <a href="<?php echo e(route('admin.settings.hotpicks')); ?>" 
        class="block bg-yellow-100 dark:bg-gray-900 hover:bg-yellow-200 dark:hover:bg-yellow-800 text-yellow-800 dark:text-yellow-300 p-4 rounded shadow transition">
            <h2 class="text-lg font-semibold mb-2">Hot Pick Numbers</h2>
            <p class="text-sm">Control hot pick numbers for game balancing.</p>
        </a>


        <!-- Future Setting Placeholder -->
        <div class="block bg-gray-100 dark:bg-gray-700 p-4 rounded shadow opacity-50 cursor-not-allowed text-gray-600 dark:text-gray-400">
            <h2 class="text-lg font-semibold mb-2">[Future Setting]</h2>
            <p class="text-sm">Coming soon...</p>
        </div>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/u355685815/domains/orcasbettingapp.com/resources/views/admin/settings/index.blade.php ENDPATH**/ ?>