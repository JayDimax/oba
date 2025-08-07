

<?php $__env->startSection('title', 'Hot Pick Settings'); ?>

<?php $__env->startSection('content'); ?>
<div x-data="{ showEditModal: false, formData: {} }">

    <div class="grid grid-cols-12 gap-6 mb-6">

        
        <div class="col-span-12 md:col-span-4">
            <div class="bg-white dark:bg-gray-900 p-6 rounded shadow border dark:border-gray-700">
                <h2 class="text-lg font-bold mb-4 text-gray-800 dark:text-white">Set Limit per Game Type</h2>

                <?php if(session('success')): ?>
                    <div class="mb-4 text-green-600 dark:text-green-400 font-semibold"><?php echo e(session('success')); ?></div>
                <?php endif; ?>

                <form action="<?php echo e(route('admin.settings.hotpicks.update')); ?>" method="POST">
                    <?php echo csrf_field(); ?>

                    <div class="mb-4">
                        <label for="game_type" class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">Game Type</label>
                        <select name="game_type" id="game_type" class="w-full border rounded px-3 py-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                            <option value="L2">Last 2 (L2)</option>
                            <option value="S3">Swer 3 (S3)</option>
                            <option value="4D">4 Digits (4D)</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="limit" class="block mb-1 font-semibold text-gray-700 dark:text-gray-300">Limit Value</label>
                        <input type="number" name="limit" id="limit"
                               class="w-full border rounded px-3 py-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                    </div>

                    <button type="submit"
                            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition text-sm font-semibold">
                        Save Limit
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
                            <th class="px-4 py-3">Game Type</th>
                            <th class="px-4 py-3">Limit Bets</th>
                            <th class="px-4 py-3">Last Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $hotpicks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 border-b dark:border-gray-700 text-gray-700 dark:text-gray-200">
                                <td class="px-4 py-2"><?php echo e($index + 1); ?></td>
                                <td class="px-4 py-2 font-bold"><?php echo e($row->game_type); ?></td>
                                <td class="px-4 py-2 text-blue-600 dark:text-blue-400"><?php echo e($row->limit); ?></td>
                                <td class="px-4 py-2 text-gray-500 dark:text-gray-400">
                                    <?php echo e(\Carbon\Carbon::parse($row->updated_at)->format('F j, Y H:i')); ?>

                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="4" class="px-4 py-3 text-center text-gray-500 dark:text-gray-400">
                                    No limits configured yet.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <div class="mt-4 px-4 dark:text-white">
                    <?php echo e($hotpicks->links()); ?>

                </div>
            </div>
        </div>

    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH F:\laragon\www\oba\resources\views/admin/settings/hotpicks.blade.php ENDPATH**/ ?>