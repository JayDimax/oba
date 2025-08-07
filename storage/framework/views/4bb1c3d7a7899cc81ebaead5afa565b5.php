

<?php $__env->startSection('title', 'Agent Commissions'); ?>

<?php $__env->startSection('content'); ?>
<div x-data="{ showEditModal: false, formData: {} }">

    <div class="grid grid-cols-12 gap-6 mb-6">

        
        <div class="col-span-12 md:col-span-4">
            <div class="bg-white dark:bg-gray-900 dark:border-gray-700 p-6 rounded shadow border">
                <h2 class="text-lg font-bold mb-4 text-gray-800 dark:text-white">Assign Commission per Agent</h2>

                <?php if(session('success')): ?>
                    <div class="mb-4 text-green-600 dark:text-green-400 font-semibold"><?php echo e(session('success')); ?></div>
                <?php endif; ?>

                <form action="<?php echo e(route('admin.settings.commissions.update')); ?>" method="POST">
                    <?php echo csrf_field(); ?>

                    <div class="mb-4">
                        <label for="agent_id" class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">Agent</label>
                        <select name="agent_id" id="agent_id" class="w-full border rounded px-3 py-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                            <?php $__currentLoopData = $agents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $agent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($agent->id); ?>"><?php echo e($agent->name); ?> (<?php echo e($agent->agent_code); ?>)</option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="game_type" class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">Game Type</label>
                        <select name="game_type" id="game_type" class="w-full border rounded px-3 py-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                            <option value="L2">Last 2</option>
                            <option value="S3">Swer 3</option>
                            <option value="4D">4 Digits</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="commission_percent" class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">Commission %</label>
                        <input type="number" name="commission_percent" id="commission_percent" step="0.01" min="0"
                               class="w-full border rounded px-3 py-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                               placeholder="e.g. 5 for 5%" required>
                    </div>

                    <button type="submit"
                            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition text-sm font-semibold">
                        Save Commission
                    </button>
                </form>
            </div>
        </div>

        
        <div class="col-span-12 md:col-span-8">
            <div class="bg-white dark:bg-gray-900 shadow rounded-lg border border-gray-200 dark:border-gray-700 overflow-x-auto">
                <table class="w-full table-auto text-sm text-left">
                    <thead class="bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 uppercase text-xs border-b dark:border-gray-700">
                        <tr>
                            <th class="px-4 py-3">#</th>
                            <th class="px-4 py-3">Agent</th>
                            <th class="px-4 py-3">Game Type</th>
                            <th class="px-4 py-3">Commission (%)</th>
                            <th class="px-4 py-3">Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $commissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 border-b dark:border-gray-700 text-gray-700 dark:text-gray-200">
                                <td class="px-4 py-2"><?php echo e($index + 1); ?></td>
                                <td class="px-4 py-2">
                                    <?php echo e($row->agent->name); ?><br>
                                    <span class="text-xs text-gray-500 dark:text-gray-400"><?php echo e($row->agent->agent_code); ?></span>
                                </td>
                                <td class="px-4 py-2"><?php echo e($row->game_type); ?></td>
                                <td class="px-4 py-2 text-green-600 dark:text-green-400"><?php echo e($row->commission_percent); ?>%</td>
                                <td class="px-4 py-2 text-gray-500 dark:text-gray-400"><?php echo e($row->updated_at->format('F j, Y H:i')); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5" class="px-4 py-3 text-center text-gray-500 dark:text-gray-400">No commission data yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/u355685815/domains/orcasbettingapp.com/resources/views/admin/settings/commissions.blade.php ENDPATH**/ ?>