

<?php $__env->startSection('title', 'Receipts'); ?>

<?php $__env->startSection('content'); ?>
<h2 class="text-xl font-bold mb-4">Receipts by Stub</h2>
<form method="GET" class="flex flex-wrap gap-4 mb-6 items-end" id="filterForm">
    
    <div>
        <label for="agent_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Agent</label>
        <select name="agent_id" id="agent_id" class="border px-3 py-1 rounded dark:bg-gray-800 dark:text-white">
            <option value="">All Agents</option>
            <?php $__currentLoopData = $agents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $agent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($agent->id); ?>" <?php echo e($selectedAgentId == $agent->id ? 'selected' : ''); ?>>
                    <?php echo e($agent->name); ?>

                </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>

    
    <div>
        <label for="filter_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Select Date</label>
        <input
            type="date"
            name="filter_date"
            id="filter_date"
            value="<?php echo e(request('filter_date', now()->toDateString())); ?>"
            class="border px-3 py-1 rounded dark:bg-gray-800 dark:text-white"
            onchange="document.getElementById('filterForm').submit()"
        >
    </div>

    
    <div>
        <label for="stub_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Stub ID</label>
        <input
            type="text"
            name="stub_id"
            id="stub_id"
            value="<?php echo e(old('stub_id', $filterStubId)); ?>"
            class="border px-3 py-1 rounded dark:bg-gray-800 dark:text-white"
            placeholder="Enter stub ID"
        >
    </div>


    
    <div>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1.5 rounded">
            Filter
        </button>
    </div>
</form>



<div class="overflow-x-auto bg-white dark:bg-gray-800 rounded shadow border border-gray-200 dark:border-gray-700">
    <table class="w-full text-sm text-left">
        <thead class="bg-gray-100 dark:bg-gray-700 dark:text-white">
            <tr>
                <th class="p-2">#</th>
                <th class="p-2">Stub ID</th>
                <th class="p-2">Game Type</th>
                <th class="p-2">Agent</th>
                <th class="p-2">Total Bets</th>
                <th class="p-2">Date</th>
                <th class="p-2">Amount</th>
                <th class="p-2 text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
       <?php $__empty_1 = true; $__currentLoopData = $stubs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $bet = $representativeBets[$stub->stub_id] ?? null;
            ?>
            <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="p-2 font-mono"><?php echo e($loop->iteration); ?></td>
                <td class="p-2 font-mono text-blue-600"><?php echo e($stub->stub_id); ?></td>
                <td class="p-2 font-mono"><?php echo e($bet?->game_type ?? 'N/A'); ?></td>
                <td class="p-2"><?php echo e($agents->firstWhere('id', $stub->agent_id)?->name ?? 'Unknown'); ?></td>
                <td class="p-2"><?php echo e($stub->total_bets); ?></td>
                <td class="p-2 text-sm text-gray-500"><?php echo e(\Carbon\Carbon::parse($stub->latest)->format('M d, Y H:i')); ?></td>
                <td class="p-2 text-left">₱<?php echo e(number_format($stub->total_amount, 2)); ?></td>
                <td class="p-2 text-center">
                    <a href="<?php echo e(route('cashier.receipt.printStub', $stub->stub_id)); ?>"
                    class="inline-block bg-indigo-600 text-white px-4 py-1 rounded hover:bg-indigo-700">Print</a>
                    <!-- <a href="<?php echo e(route('cashier.receipts.export', ['agent_id' => request('agent_id')])); ?>"
                    class="inline-block bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">PDF</a> -->
                </td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="8" class="text-center p-4 text-gray-500">No receipts found.</td>
            </tr>
        <?php endif; ?>


        </tbody>
    </table>
</div>

<div class="mt-4">
    <?php echo e($stubs->links()); ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.cashier', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/u355685815/domains/orcasbettingapp.com/resources/views/cashier/receipts/index.blade.php ENDPATH**/ ?>