

<?php $__env->startSection('title', 'Dashboard'); ?>

<?php $__env->startSection('content'); ?>

<div class="grid grid-cols-1 md:grid-cols-4 gap-6">

    <div class="bg-white dark:bg-gray-900 dark:border dark:border-gray-700 shadow rounded p-4">
        <h3 class="text-sm text-gray-500 dark:text-gray-400">Total Bets Today</h3>
        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">₱<?php echo e(number_format($totalBetsToday, 2)); ?></p>
    </div>

    <div class="bg-white dark:bg-gray-900 dark:border dark:border-gray-700 shadow rounded p-4">
        <h3 class="text-sm text-gray-500 dark:text-gray-400">Total Collected</h3>
        <p class="text-2xl font-bold text-green-600 dark:text-green-400">₱<?php echo e(number_format($totalCollected, 2)); ?></p>
    </div>



    <div class="bg-white dark:bg-gray-900 dark:border dark:border-gray-700 shadow rounded p-4">
        <h3 class="text-sm text-gray-500 dark:text-gray-400">Pending Agents</h3>
        <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400"><?php echo e($pendingAgentsCount); ?></p>
    </div>

    <!-- <div class="bg-white dark:bg-gray-900 dark:border dark:border-gray-700 shadow rounded p-4">
        <?php $__currentLoopData = $agentRemittances; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="bg-white dark:bg-gray-800 shadow p-4 rounded border-l-4 <?php echo e($data['has_results'] ? 'border-blue-600' : 'border-yellow-500'); ?>">
                <div class="text-sm font-semibold text-gray-700 dark:text-gray-100">
                    <?php echo e($data['name']); ?>

                </div>
                <?php if($data['has_results']): ?>
                    <div class="text-blue-700 dark:text-blue-300 text-xl font-bold mt-1">
                        ₱<?php echo e(number_format($data['amount'], 2)); ?>

                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Net Remittance (based on declared results)
                    </div>
                <?php else: ?>
                    <div class="text-yellow-600 dark:text-yellow-300 text-sm mt-1 italic">
                        Waiting for results (2PM, 5PM, 9PM)
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div> -->
</div>
    
    <?php if($agentsWithSystemDeficit->count()): ?>
        <div class="bg-red-100 text-red-800 p-4 rounded mb-4 border border-red-300">
            <strong>⚠️ System Deficit Detected</strong>
            <ul class="mt-2 list-disc pl-5">
                <?php $__currentLoopData = $agentsWithSystemDeficit; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li>
                        Agent <strong><?php echo e($entry['agent']->name ?? 'Unknown'); ?></strong> has a system deficit of 
                        <strong>₱<?php echo e(number_format(abs($entry['system_deficit']), 2)); ?></strong>
                        due to high payouts/incentives.
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

<div class="mt-8">
    <h4 class="text-lg font-semibold mb-3 text-gray-800 dark:text-gray-100">Agents with Pending Remittance</h4>
    <ul class="bg-white dark:bg-gray-900 dark:border dark:border-gray-700 divide-y divide-gray-200 dark:divide-gray-700 rounded shadow">
        <?php $__empty_1 = true; $__currentLoopData = $pendingAgents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $agent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <li class="p-4 flex justify-between items-center text-gray-800 dark:text-gray-200">
                <span class="font-medium"><?php echo e($agent->name); ?></span>
                <span class="text-red-500 dark:text-red-400 font-semibold">₱<?php echo e(number_format($agent->unpaid_amount, 2)); ?></span>
            </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <li class="p-4 text-center text-gray-500 dark:text-gray-400">All remittances are settled.</li>
        <?php endif; ?>
    </ul>
</div>


<!-- filter -->
<div class="max-w-7xl mx-auto px-4 py-6">
    <form method="GET" action="<?php echo e(route('cashier.dashboard')); ?>" class="mb-6 flex items-end gap-4 flex-wrap">
        <div>
            <label for="game_date" class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Select Game Date</label>
            <input type="date" name="game_date" id="game_date"
                value="<?php echo e($gameDate); ?>"
                class="border rounded px-3 py-2 text-sm shadow focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
        </div>
        <div>
            <button type="submit"
                    class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                Filter
            </button>
        </div>
    </form>

    <!-- table -->
    <h1 class="text-xl font-bold mb-4 text-gray-900 dark:text-gray-100">🧾 Agent Remittance Summary</h1>

    <?php $__empty_1 = true; $__currentLoopData = $agents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $agent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="bg-white dark:bg-gray-900 dark:border dark:border-gray-700 shadow rounded-lg p-4 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white"><?php echo e($agent->name); ?></h2>

            <?php if($agent->collections->isEmpty()): ?>
                <p class="text-gray-500 dark:text-gray-400 text-sm mt-2">No remittances found.</p>
            <?php else: ?>
                <table class="w-full mt-4 text-sm text-left text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 rounded">
                    <thead class="text-xs uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-300">
                        <tr>
                            <th class="px-4 py-2 border-b border-gray-300 dark:border-gray-600">Date</th>
                            <th class="px-4 py-2 border-b border-gray-300 dark:border-gray-600">Gross Sales</th>
                            <!-- <th class="px-4 py-2 border-b border-gray-300 dark:border-gray-600">Amount Due</th>  -->
                            <th class="px-4 py-2 border-b border-gray-300 dark:border-gray-600">Total Remitted</th>
                            <th class="px-4 py-2 border-b border-gray-300 dark:border-gray-600">Status</th>
                            <th class="px-4 py-2 border-b border-gray-300 dark:border-gray-600">Verified By</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800">
                        <?php $__currentLoopData = $agent->collections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $collection): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="px-4 py-2 border-b border-gray-300 dark:border-gray-600"><?php echo e(\Carbon\Carbon::parse($collection->collection_date)->toFormattedDateString()); ?></td>
                                <td class="px-4 py-2 border-b border-gray-300 dark:border-gray-600">₱<?php echo e(number_format($collection->gross, 2)); ?></td>
                                <!-- <td class="px-4 py-2 border-b border-gray-300 dark:border-gray-600">₱<?php echo e(number_format($totalNetCollected, 2)); ?></td> -->
                                <td class="px-4 py-2 border-b border-gray-300 dark:border-gray-600">₱<?php echo e(number_format($agent->total_incoming_remittance, 2)); ?></td>
                                <td class="px-4 py-2 border-b border-gray-300 dark:border-gray-600">
                                    <span class="px-2 py-1 rounded
                                        <?php if($collection->status === 'approved'): ?> bg-green-200 text-green-800 dark:bg-green-800 dark:text-green-200
                                        <?php elseif($collection->status === 'pending'): ?> bg-yellow-200 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-200
                                        <?php else: ?> bg-red-200 text-red-800 dark:bg-red-800 dark:text-red-200 <?php endif; ?>">
                                        <?php echo e(ucfirst($collection->status)); ?>

                                    </span>
                                </td>
                                <td class="px-4 py-2 border-b border-gray-300 dark:border-gray-600">
                                    <?php echo e(optional($collection->verifiedBy)->name ?? '—'); ?>

                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <p class="text-gray-600 dark:text-gray-300">No agents found.</p>
    <?php endif; ?>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.cashier', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/u355685815/domains/orcasbettingapp.com/resources/views/cashier/dashboard.blade.php ENDPATH**/ ?>