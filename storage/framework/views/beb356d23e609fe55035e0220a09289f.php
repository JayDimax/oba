<div
    x-show="showCreateModal"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 scale-95"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
    class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center"
    x-cloak
>
    <div @click.away="showCreateModal = false"
         class="bg-white dark:bg-gray-900 w-full max-w-md p-6 rounded shadow-lg border border-gray-200 dark:border-gray-700">

        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">Add New User</h3>

        <form action="<?php echo e(route('admin.users.store')); ?>" method="POST" class="space-y-4">
            <?php echo csrf_field(); ?>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name</label>
                <input name="name" required
                       class="w-full px-3 py-2 bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded text-sm focus:outline-none focus:ring focus:ring-blue-200" />
            </div>

            <div>
                <label class="block font-medium mb-1 dark:text-gray-300">User Code</label>
                <input name="agent_code"
                    value="<?php echo e($agentCode ?? ''); ?>"
                    readonly
                    class="w-full bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded px-3 py-2 text-gray-700 dark:text-white cursor-not-allowed" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role</label>
                <select name="role" required
                        class="w-full px-3 py-2 bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded text-sm focus:outline-none focus:ring focus:ring-blue-200">
                    <option value="">-- Select Role --</option>
                    <option value="agent">Agent</option>
                    <option value="cashier">Cashier</option>
                </select>
            </div>

            <div>
                <label class="inline-flex items-center space-x-2">
                    <input type="checkbox" name="set_default_password" checked
                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                    <span class="text-sm text-gray-700 dark:text-gray-300">Set default password to <code>password</code></span>
                </label>
            </div>

            <div class="flex justify-end gap-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                <button type="button" @click="showCreateModal = false"
                        class="text-sm text-gray-600 dark:text-gray-300 hover:text-gray-800 px-4 py-2">
                    Cancel
                </button>
                <button type="submit"
                        class="text-sm px-5 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Create User
                </button>
            </div>
        </form>
    </div>
</div>
<?php /**PATH F:\laragon\www\oba\resources\views/admin/users/partials/create-modal.blade.php ENDPATH**/ ?>