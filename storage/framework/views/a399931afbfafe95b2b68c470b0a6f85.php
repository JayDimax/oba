

<?php $__env->startSection('content'); ?>
<div class="px-4 sm:px-6 py-6 bg-gray-50 dark:bg-gray-900 min-h-screen">
    <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-gray-100">Admin Dashboard</h1>

    
    <div class="grid grid-cols-1 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-10">

        
        <section class="w-full bg-white dark:bg-gray-800 p-4 rounded shadow">
            <h2 class="font-semibold mb-4 text-gray-900 dark:text-gray-100">Today's Summary</h2>

            <div class="flex justify-between text-gray-800 dark:text-gray-300">
                <span>Total Gross Bets:</span>
                <span>₱<?php echo e(isset($todaySummary['gross']) ? number_format($todaySummary['gross'], 2) : '0.00'); ?></span>
            </div>

            <div class="flex justify-between text-gray-800 dark:text-gray-300">
                <span>Total Winnings:</span>
                <span>₱<?php echo e(isset($todaySummary['totalWinnings']) ? number_format($todaySummary['totalWinnings'], 2) : '0.00'); ?></span>
            </div>

            <div class="flex justify-between text-gray-800 dark:text-gray-300">
                <span>Expected Remittance:</span>
                <span>₱<?php echo e(isset($todaySummary['computed']) ? number_format($todaySummary['computed'], 2) : '0.00'); ?></span>
            </div>

            <div class="flex justify-between text-gray-800 dark:text-gray-300">
                <span>Actual Remittance:</span>
                <span>₱<?php echo e(isset($actualRemittance) ? number_format($actualRemittance, 2) : '0.00'); ?></span>
            </div>

            <div class="flex justify-between text-red-600 dark:text-red-400">
                <span>Unremitted Balance:</span>
                <span>
                    ₱<?php echo e(isset($unremittedBalance) ? number_format($unremittedBalance, 2) : '0.00'); ?>

                </span>
            </div>
        </section>


        
        <section class="w-full bg-white dark:bg-gray-800 p-4 rounded shadow">

            <h2 class="font-semibold mb-4 text-gray-900 dark:text-gray-100">Agent Status</h2>
            <div class="flex justify-between text-gray-800 dark:text-gray-300"><span>Total Agents:</span><span><?php echo e(isset($totalAgents) ? $totalAgents : 'N/A'); ?></span></div>
            <div class="flex justify-between text-gray-800 dark:text-gray-300"><span>Active Agents:</span><span><?php echo e(isset($activeAgents) ? $activeAgents : 'N/A'); ?></span></div>
            <div class="flex justify-between text-gray-800 dark:text-gray-300"><span>Blocked Agents:</span><span><?php echo e(isset($blockedAgents) ? $blockedAgents : 'N/A'); ?></span></div>
            <div class="flex justify-between text-red-600 dark:text-red-400"><span>Agents With Balance:</span><span><?php echo e(isset($agentsWithBalance) ? $agentsWithBalance : 'N/A'); ?></span></div>
        </section>

            
            <?php
                $drawTimes = [
                    '14' => '2PM',
                    '17' => '5PM',
                    '21' => '9PM',
                ];
                $gameTypes = ['L2', 'S3', '4D'];
            ?>

            <section class="w-full bg-white dark:bg-gray-800 p-4 rounded shadow">
                <h2 class="font-semibold mb-4 text-gray-900 dark:text-gray-100">Draw Status</h2>

                
                <div class="flex justify-between text-gray-800 dark:text-gray-300 mb-2">
                    <span>Next Draw:</span>
                    <span><?php echo e($nextDraw['label'] ?? 'No more draws today'); ?></span>
                </div>

                
                <?php $__currentLoopData = $drawTimes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $time => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex justify-between items-start text-gray-800 dark:text-gray-300 mb-1">
                        
                        <span class="font-semibold w-1/3"><?php echo e($label); ?>:</span>

                        
                        <span class="w-2/3 text-right">
                            <?php $__currentLoopData = $gameTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $match = ($allResults ?? collect())->firstWhere(function ($result) use ($time, $type) {
                                        return $result->game_draw == $time && $result->game_type == $type;
                                    });
                                ?>
                                <?php echo e($type); ?> -
                                <?php if($match): ?>
                                    <?php echo e($match->winning_combination); ?>

                                <?php else: ?>
                                    <span class="italic text-sm text-yellow-600 dark:text-yellow-400">Pending</span>
                                <?php endif; ?>
                                <?php if(!$loop->last): ?>
                                    <span class="mx-1">|</span>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </section>



    </div>


    
    <?php if(isset($deficit) && $deficit > 0): ?>

    <section class="bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-700 text-red-800 dark:text-black p-4 rounded shadow col-span-1 md:col-span-2 lg:col-span-3">
        <div class="flex items-center gap-2 mb-2">
            <svg class="w-5 h-5 text-red-600 dark:text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M12 5a7 7 0 100 14 7 7 0 000-14z" />
            </svg>
            <h2 class="font-semibold text-lg dark:text-white">System Deficit Detected</h2>
        </div>
        <p class="text-sm dark:text-white">
            The system has recorded a deficit for today. Gross sales are not enough to cover total winnings and incentives.
        </p>
        <p class="mt-2 text-base font-semibold dark:text-white">
            Deficit Amount: ₱<?php echo e(number_format(abs($deficit), 2)); ?>

        </p>
    </section>
    <?php endif; ?>

    
    <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow mt-10">


        
        <div class="flex justify-end mb-6">
            <div class="w-full md:w-1/2 lg:w-1/3 bg-gray-100 dark:bg-gray-700 p-4 rounded shadow">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">Net Sales</h2>
                    <p class="text-3xl text-yellow font-semibold text-right">₱<?php echo e(isset($netSales) ? number_format($netSales, 2) : '0:00'); ?></p>
                </div>
            </div>
        </div>
        
        <div class="flex flex-col">
            <h3 class="text-md font-bold text-center text-gray-900 dark:text-white mb-3">
                Top 3 Combinations Today
            </h3>

            <div class="flex flex-wrap gap-4">
                <?php $__currentLoopData = ['L2' => '🎯', 'S3' => '🎰', '4D' => '🔢']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $icon): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex-1 min-w-[150px] max-w-full bg-white dark:bg-gray-800 p-4 rounded shadow">
                    <h4 class="text-md font-bold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                        <span class="text-lg"><?php echo e($icon); ?></span>
                        <span><?php echo e($type); ?></span>
                    </h4>

                    <?php if(isset($topCombinations[$type]) && $topCombinations[$type]->count()): ?>
                    <?php $__currentLoopData = $topCombinations[$type]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="bg-gray-200 dark:bg-gray-700 px-3 py-1 mb-1 rounded font-mono text-center">
                        <?php echo e($bet->bet_number); ?>

                        <span class="text-xs font-semibold">×<?php echo e($bet->total); ?></span>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php else: ?>
                    <div class="text-gray-400 text-sm">No data</div>
                    <?php endif; ?>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>
    
    <hr class="border border-blue-600 dark:border-blue-400 mt-10 mb-4">

    <form method="GET" action="<?php echo e(route('admin.admindashboard')); ?>" class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
        <div>
            <label class="block text-gray-700 dark:text-gray-200">From Date</label>
            <input type="date" name="from_date" value="<?php echo e(old('from_date', $from ?? request('from_date', today()->toDateString()))); ?>"
                class="w-full mt-1 p-2 border rounded dark:bg-gray-800 dark:text-white dark:border-gray-600">
        </div>

        <div>
            <label class="block text-gray-700 dark:text-gray-200">To Date</label>
            <input type="date" name="to_date" value="<?php echo e(request('to_date', $to)); ?>"
                class="w-full mt-1 p-2 border rounded dark:bg-gray-800 dark:text-white dark:border-gray-600">
        </div>

        <div>
            <label class="block text-gray-700 dark:text-gray-200">Draw Time</label>
            <select name="draw_time" class="w-full mt-1 p-2 border rounded dark:bg-gray-800 dark:text-white dark:border-gray-600">
                <option value="ALL" <?php echo e(request('draw_time', 'ALL') === 'ALL' ? 'selected' : ''); ?>>All</option>
                <option value="2PM" <?php echo e(request('draw_time') === '2PM' ? 'selected' : ''); ?>>2PM</option>
                <option value="5PM" <?php echo e(request('draw_time') === '5PM' ? 'selected' : ''); ?>>5PM</option>
                <option value="9PM" <?php echo e(request('draw_time') === '9PM' ? 'selected' : ''); ?>>9PM</option>
            </select>
        </div>

        <div>
            <label class="block text-gray-700 dark:text-gray-200">Agent Name</label>
            <input type="text" name="agent_name" value="<?php echo e(request('agent_name')); ?>"
                placeholder="Search agent..."
                class="w-full mt-1 p-2 border rounded dark:bg-gray-800 dark:text-white dark:border-gray-600">
        </div>

        <div class="md:col-span-4 flex gap-3 mt-2">
            <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                Apply Filters
            </button>
            <a href="<?php echo e(route('admin.admindashboard')); ?>"
            class="px-4 py-2 bg-gray-300 dark:bg-gray-700 dark:text-white text-gray-800 rounded hover:bg-gray-400 dark:hover:bg-gray-600 transition">
                Reset
            </a>
        </div>
    </form>
    
    
    <?php if(request()->anyFilled(['from_date', 'to_date', 'draw_time', 'agent_name'])): ?>
    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        <strong>Active Filters:</strong>
        <?php if(request('from_date')): ?> From: <?php echo e(request('from_date')); ?> <?php endif; ?>
        <?php if(request('to_date')): ?> To: <?php echo e(request('to_date')); ?> <?php endif; ?>
        <?php if(request('draw_time')): ?> Draw: <?php echo e(request('draw_time')); ?> <?php endif; ?>
        <?php if(request('agent_name')): ?> Agent: <?php echo e(request('agent_name')); ?> <?php endif; ?>

        <a href="<?php echo e(route('admin.admindashboard')); ?>"
            class="ml-2 text-blue-500 hover:underline">Reset</a>
    </div>
    <?php endif; ?>

    
    <div class="overflow-x-auto">
        <table class="min-w-full table-auto border border-gray-300 dark:border-gray-700 text-sm">
            <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 font-semibold">
                <tr>
                    <th class="px-4 py-2 text-left">Agent Code</th>
                    <th class="px-4 py-2 text-left">Game Time</th>
                    <th class="px-4 py-2 text-left">Game Type</th>
                    <th class="px-4 py-2 text-left">Bet #</th>
                    <th class="px-4 py-2 text-right">Bet Amount</th>
                    <th class="px-4 py-2 text-center">Date</th>
                </tr>
            </thead>
            <tbody class="text-gray-900 dark:text-gray-100">
                <?php if(isset($bets)): ?>
                <?php $__empty_1 = true; $__currentLoopData = $bets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-900">
                    <td class="px-4 py-2"><?php echo e($bet->betAgent->agent_code ?? '—'); ?></td>
                    <td class="px-4 py-2"><?php echo e(\Carbon\Carbon::createFromTime($bet->game_draw)->format('g:i A')); ?></td>
                    <td class="px-4 py-2"><?php echo e($bet->game_type); ?></td>
                    <td class="px-4 py-2"><?php echo e($bet->bet_number); ?></td>
                    <td class="px-4 py-2 text-right">₱<?php echo e(number_format($bet->amount, 2)); ?></td>
                    <td class="px-4 py-2 text-center"><?php echo e($bet->game_date ?? '—'); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="6" class="text-center py-4 text-gray-500 dark:text-gray-400">
                        No bets found for this filter.
                    </td>
                </tr>
                <?php endif; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="mt-4"> 
        <?php echo e($bets->withQueryString()->links()); ?>

    </div>

    
    <div class="text-center my-4 flex justify-center gap-4">
        
        <a href="<?php echo e(route('admin.admindashboard', array_merge(request()->all(), ['print' => 'yes']))); ?>"
        target="_blank"
        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-md transition duration-300 ease-in-out">
            🖨 Print Report
        </a>
 
        
        <a href="<?php echo e(route('admin.export-bets', request()->all())); ?>"
        class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg shadow-md transition duration-300 ease-in-out">
            📥 Export to Excel
        </a>
    </div>


</div>





<style>
    @media print {
        body * {
            visibility: hidden;
        }

        .p-4.bg-white.rounded-lg.shadow,
        .p-4.bg-white.rounded-lg.shadow * {
            visibility: visible;
        }

        .p-4.bg-white.rounded-lg.shadow {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }

        form,
        button,
        nav,
        .no-print {
            display: none !important;
        }
    }
</style>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/u355685815/domains/orcasbettingapp.com/resources/views/admin/dashboard.blade.php ENDPATH**/ ?>