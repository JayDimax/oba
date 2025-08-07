

<?php $__env->startSection('title', 'Sales Reports'); ?>

<?php $__env->startSection('content'); ?>
<div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow mb-6">

    <h1 class="text-2xl font-bold mb-6 text-gray-900 dark:text-white">Sales Reports</h1>

    
    <div class="mb-4 flex space-x-4 border-b border-gray-300 dark:border-gray-700">
        <a href="<?php echo e(route('admin.reports.index', ['tab' => 'daily'])); ?>"
            class="pb-2 border-b-2 <?php echo e($tab === 'daily' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-500'); ?>">
            Daily
        </a>
        <a href="<?php echo e(route('admin.reports.index', ['tab' => 'weekly'])); ?>"
            class="pb-2 border-b-2 <?php echo e($tab === 'weekly' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-500'); ?>">
            Weekly
        </a>
        <a href="<?php echo e(route('admin.reports.index', ['tab' => 'monthly'])); ?>"
            class="pb-2 border-b-2 <?php echo e($tab === 'monthly' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-500'); ?>">
            Monthly
        </a>
        <a href="<?php echo e(route('admin.reports.index', ['tab' => 'yearly'])); ?>"
            class="pb-2 border-b-2 <?php echo e($tab === 'yearly' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-500'); ?>">
            Yearly
        </a>
    </div>


    
    <form action="<?php echo e(route('admin.reports.print')); ?>" method="GET" target="_blank" class="mt-4 text-right">
        <input type="hidden" name="type" value="<?php echo e($tab); ?>">
        <?php if($tab === 'daily'): ?>
        <input type="hidden" name="value" value="<?php echo e($filterDate); ?>">
        <?php elseif($tab === 'weekly'): ?>
        <input type="hidden" name="value" value="<?php echo e($filterWeek); ?>">
        <?php elseif($tab === 'monthly'): ?>
        <input type="hidden" name="value" value="<?php echo e($filterMonth); ?>">
        <?php elseif($tab === 'yearly'): ?>
        <input type="hidden" name="value" value="<?php echo e($filterYear); ?>">
        <?php endif; ?>
        <button type="submit" class="flex items-center gap-2 px-4 py-2 mb-2 border border-transparent rounded shadow transition duration-200
            bg-blue-600 hover:bg-blue-700 text-white
            dark:bg-blue-700 dark:hover:bg-blue-800 dark:text-white">
            
            <!-- Lucide Print Icon (Inline SVG) -->
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v8H6v-8z" />
            </svg>
            Print Report
        </button>

    </form>



    <?php
    $filterType = request('type', 'daily'); // default to daily if not set
    $filterValue = match($filterType) {
    'daily' => request('date'),
    'weekly' => request('filter_week'),
    'monthly' => request('month'),
    'yearly' => request('year'),
    default => null,
    };
    ?>



<table class="w-full table-auto border border-gray-300 dark:border-gray-700 rounded overflow-hidden text-sm">
    <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 font-semibold border-b border-gray-300 dark:border-gray-600">
        <tr>
            <th class="px-4 py-2 text-left">Agent Name</th>
            <th class="px-4 py-2 text-center">Gross Sales</th>
            <th class="px-4 py-2 text-center">Net Remittance</th>
            <th class="px-4 py-2 text-center">Balance</th>
            <th class="px-4 py-2 text-center">Status</th>
        </tr>
    </thead>
    <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $reportData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-900">
            <td class="px-4 py-2 text-gray-900 dark:text-gray-100"><?php echo e($row['agent']->name); ?></td>
            <td class="px-4 py-2 text-center text-gray-900 dark:text-gray-100"><?php echo e(number_format($row['gross_sales'], 2)); ?></td>
            <td class="px-4 py-2 text-center text-gray-900 dark:text-gray-100"><?php echo e(number_format($row['net_remittance'], 2)); ?></td>
            <td class="px-4 py-2 text-center text-gray-900 dark:text-gray-100"><?php echo e(number_format($row['difference'], 2)); ?></td>
            <td class="px-4 py-2 text-center">
                <?php if(abs($row['difference']) < 0.01): ?>
                    <span class="text-green-600 font-semibold">Balanced</span>
                    <?php else: ?>
                    <span class="text-red-600 font-semibold">Under</span>
                    <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr>
            <td colspan="5" class="text-center py-4 text-gray-500 dark:text-gray-400">No data found for this period.</td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>
</div>
<div class="bg-white dark:bg-gray-800 p-4 rounded shadow my-6">
    <h2 class="text-lg font-semibold text-purple-700 dark:text-purple-300 mb-4">Income Calendar</h2>
    <div id="calendar">
    
    <div class="mt-6 grid grid-cols-2 gap-4 text-sm">
        <?php $__currentLoopData = $grossPerDay; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $date => $gross): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $net = $netPerDay[$date] ?? 0;
            ?>
            <div class="p-2 border rounded bg-gray-50 dark:bg-gray-700">
                <div class="font-semibold"><?php echo e(\Carbon\Carbon::parse($date)->format('M d, Y (D)')); ?></div>
                <div><span class="text-purple-600 font-semibold">Gross:</span> ₱<?php echo e(number_format($gross, 2)); ?></div>
                <div><span class="text-green-600 font-semibold">Net:</span> ₱<?php echo e(number_format($net, 2)); ?></div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
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

        /* Hide filter form and print button on print */
        form,
        button[onclick="window.print()"] {
            display: none !important;
        }
    }
    /* Always use black text for weekday names */
.fc .fc-col-header-cell-cushion {
    color: black !important;
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        events: <?php echo json_encode($calendarIncome, 15, 512) ?>,
        height: 'auto',
        eventColor: '#6b46c1',
        eventTextColor: 'white',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: ''
        },
        eventDidMount: function(info) {
            const props = info.event.extendedProps;
            info.el.setAttribute('title', `Gross: ₱${props.gross.toFixed(2)}\nCommission: ₱${props.commission.toFixed(2)}\nWinnings: ₱${props.winnings.toFixed(2)}`);
        }
    });
    calendar.render();
});
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/u355685815/domains/orcasbettingapp.com/resources/views/admin/reports/index.blade.php ENDPATH**/ ?>