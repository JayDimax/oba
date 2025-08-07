<!-- resources/views/admin/users/partials/edit-modal.blade.php -->
<div
    x-show="showEditModal"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 scale-95"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
    class="fixed inset-0 z-50 bg-black backdrop-blur-sm flex items-center justify-center"
    x-cloak
>

    <div @click.away="showEditModal = false"
         class="bg-white w-full max-w-md p-6 rounded shadow-lg border border-gray-200">

        <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Edit User</h3>

        <form :action="`/admin/users/${formData.id}`" method="POST" class="space-y-4">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input x-model="formData.name" name="name" required
                       class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded text-sm focus:outline-none focus:ring focus:ring-blue-200" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Agent Code</label>
                <input x-model="formData.agent_code" name="agent_code" required
                       class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded text-sm focus:outline-none focus:ring focus:ring-blue-200" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                <select x-model="formData.role" name="role" required
                        class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded text-sm focus:outline-none focus:ring focus:ring-blue-200">
                    <option value="agent">Agent</option>
                    <option value="cashier">Cashier</option>
                </select>
            </div>

            <div>
                <label class="inline-flex items-center space-x-2">
                    <input type="checkbox" name="reset_password"
                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                    <span class="text-sm text-gray-700">Reset password to default (<code>password</code>)</span>
                </label>
            </div>

            <div class="flex justify-end gap-2 pt-2 border-t">
                <button type="button" @click="showEditModal = false"
                        class="text-sm text-gray-600 hover:text-gray-800 px-4 py-2">
                    Cancel
                </button>
                <button type="submit"
                        class="text-sm px-5 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
<?php /**PATH F:\laragon\www\oba\resources\views/admin/users/partials/edit-modal.blade.php ENDPATH**/ ?>