
    <!-- Card Form -->

        <h2 class="text-xl font-bold mb-4 text-center text-gray-800 dark:text-white">Declare Draw Result</h2>

        <?php if(session('success')): ?>
            <div class="mb-4 text-green-600 font-semibold text-center"><?php echo e(session('success')); ?></div>
        <?php endif; ?>

        <form action="<?php echo e(route('admin.results.store')); ?>" method="POST" class="space-y-6" id="declareResultForm">
            <?php echo csrf_field(); ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Game Date -->
                <div class="flex flex-col">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Game Date</label>
                    <p class="bg-gray-100 dark:bg-gray-700 rounded px-4 py-2 text-gray-800 dark:text-white">
                        <?php echo e(\Carbon\Carbon::today()->toFormattedDateString()); ?>

                    </p>
                </div>

                <!-- Game Type -->
                <div class="flex flex-col">
                    <label for="game_type" class="text-sm font-medium text-gray-700 dark:text-gray-200">Game Type</label>
                    <select id="game_type" name="game_type" class="border rounded px-3 py-2 dark:bg-gray-700 dark:text-white" required>
                        <option value="L2">Last 2</option>
                        <option value="S3">Swer 3</option>
                        <option value="4D">4 Digits</option>
                    </select>
                </div>

                <!-- Game Draw -->
                <div class="flex flex-col">
                    <label for="game_draw" class="text-sm font-medium text-gray-700 dark:text-gray-200">Draw Time</label>
                    <select id="game_draw" name="game_draw" class="border rounded px-3 py-2 dark:bg-gray-700 dark:text-white" required>
                        <option value="14:00">2 PM</option>
                        <option value="17:00">5 PM</option>
                        <option value="21:00">9 PM</option>
                    </select>
                </div>

                <!-- Winning Combination -->
                <div class="flex flex-col justify-center">
                    <label for="winning_combination" class="text-sm text-left font-sm text-gray-700 dark:text-gray-200">WINNING COMBINATIONS</label>
                    <input
                        type="text"
                        id="winning_combination"
                        name="winning_combination"
                        placeholder="Enter Here"
                        class="w-40 mt-1 px-3 py-1 rounded border text-2xl 
                        border-gray-300 bg-white text-gray-800 
                        dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                        required
                    >
                    
                </div>
            </div>

            <!-- Submit Button -->
            <div class="text-center">
                <button type="submit" id="declareBtn"
                        class="bg-blue-600 text-white px-6 py-2 rounded-md text-sm font-semibold hover:bg-blue-700 transition">
                    DECLARE RESULTS
                </button>
            </div>
        </form>






<script>
    document.addEventListener('DOMContentLoaded', function () {
        const declareBtn = document.getElementById('declareBtn');
        const gameDrawSelect = document.getElementById('game_draw');
        const warningMsg = document.getElementById('warningMsg');

        // Always enable the button
        declareBtn.disabled = false;
        declareBtn.classList.remove('opacity-50', 'cursor-not-allowed');

        // Optionally hide the warning if it's visible
        if (warningMsg) {
            warningMsg.style.display = 'none';
        }

        // Still listen to draw changes if needed, but don't restrict button
        gameDrawSelect.addEventListener('change', () => {
            declareBtn.disabled = false;
            declareBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            if (warningMsg) {
                warningMsg.style.display = 'none';
            }
        });
    });
</script>

<?php /**PATH /home/u355685815/domains/orcasbettingapp.com/resources/views/admin/results/partials/declare-form.blade.php ENDPATH**/ ?>