

<?php $__env->startSection('content'); ?>
<style>
    body {
        font-family: monospace;
        font-size: 13px;
    }

    .center {
        text-align: center;
    }

    .bold {
        font-weight: bold;
    }

    .divider {
        border-top: 1px dashed black;
        margin: 6px 0;
    }

    .section {
        margin: 4px 0;
    }

    .right {
        text-align: right;
    }

    .left {
        text-align: left;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    td {
        padding: 2px 0;
        font-size: 13px;
    }

    .label {
        text-align: left;
    }

    .value {
        text-align: right;
    }

    .highlight {
        font-size: 14px;
        font-weight: bold;
        color: #d00;
    }

    .small {
        font-size: 12px;
    }

    .hidden {
        display: none;
    }

</style>
<div class="center" style="margin-bottom: 6px;">
    <img src="<?php echo e(asset('images/tlogo.png')); ?>" alt="Logo"
         style="width: 80px; max-width: 100%; display: inline-block;">
</div>

<div class="center bold" style="font-size: 16px;">ORCAS Betting App</div>

<?php if(isset($agentName)): ?>
    <div class="center bold" style="font-size: 14px;">Agent: <?php echo e($agentName); ?></div>
<?php endif; ?>

<?php if(isset($drawDate)): ?>
    <div class="center small">Draw Date: <strong><?php echo e($drawDate); ?></strong></div>
<?php endif; ?>

<?php if(isset($draw)): ?>
    <div class="center small">
        Draw: <strong>
            <?php echo e($draw === '1st' ? '1st Draw' : ($draw === '2nd' ? '2nd Draw' : ($draw === '3rd' ? '3rd Draw' : 'All Draws') )); ?>

        </strong>
    </div>
<?php endif; ?>

<div class="divider"></div>


<table>
    <?php
        $rows = [
            'Gross Sales:'              => $summary['gross'] ?? 0,
            'Hits:'                     => $summary['hits'] ?? 0,
            'Net Sales:'                => $summary['net_sales'] ?? 0,
            'Payout Coverage (Tapada):' => $summary['payouts'] ?? 0,
            'Commission (10%):'         => $summary['commission_base'] ?? 0,
            'Incentives:'              => $summary['incentives'] ?? 0,
        ];

        if ($draw === null || $draw === 'all') {
            $rows['Deductions:'] = $summary['deductions'] ?? 0;
        }
    ?>

    <?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td class="label"><?php echo e($label); ?></td>
            <td class="value">₱<?php echo e(number_format($value, 2)); ?></td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</table>

<div class="divider"></div>


<table>
    <tr>
        <td class="label bold">Total Net (Remittance):</td>
        <td class="value highlight">₱<?php echo e(number_format($summary['net_after_payouts'] ?? 0, 2)); ?></td>
    </tr>
</table>

<div class="divider"></div>


<?php if(isset($printedTime)): ?>
    <div class="center small">Printed: <?php echo e($printedTime); ?></div>
<?php endif; ?>

<div style="height: 50px;"></div>

<script>
    window.print();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.thermalprinter', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/u355685815/domains/orcasbettingapp.com/resources/views/agent/summary-receipt.blade.php ENDPATH**/ ?>