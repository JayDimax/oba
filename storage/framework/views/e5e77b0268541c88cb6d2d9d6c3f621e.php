

<?php $__env->startSection('title', 'Change Password'); ?>

<?php $__env->startSection('content'); ?>
    <div class="max-w-xl mx-auto">
        <div class="bg-white dark:bg-gray-800 shadow rounded-xl p-6">

            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">
                Change Password
            </h2>

            
            <?php if(session('status')): ?>
                <div class="mb-4 p-3 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded">
                    <?php echo e(session('status')); ?>

                </div>
            <?php endif; ?>

            
            <form method="POST" action="<?php echo e(route('profile.admin-update')); ?>" class="space-y-5">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PATCH'); ?>

                
                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Current Password
                    </label>
                    <input type="password" name="current_password" id="current_password" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600">
                    <?php $__errorArgs = ['current_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-red-500 text-sm mt-1"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        New Password
                    </label>
                    <input type="password" name="password" id="password" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600">
                    <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-red-500 text-sm mt-1"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Confirm New Password
                    </label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:text-white dark:border-gray-600">
                </div>

                
                <div class="flex mt-2 text-center">
                    <button type="submit"
                        class="px-5 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 transition">
                        Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/u355685815/domains/orcasbettingapp.com/resources/views/profile/admin-edit.blade.php ENDPATH**/ ?>