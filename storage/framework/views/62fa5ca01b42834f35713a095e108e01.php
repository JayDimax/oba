

<?php $__env->startSection('content'); ?>
<style>
    body {
        font-family: monospace;
        font-size: 13px; /* increased from 12px */
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

    .small {
        font-size: 12px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th, td {
        padding: 4px 0;
    }

    th {
        font-size: 13px;
    }

    td {
        font-size: 18px;
    }
    
    .line {
    border-top: 1px dashed #000;
    margin: 10px 0;
    }
    .hidden {
    display: none;
    }

</style>

<div class="center" style="margin-bottom: 6px;">
    <img src="<?php echo e(asset('images/tlogo.png')); ?>" alt="Logo"
         style="width: 80px; max-width: 100%; display: inline-block;">
</div>

<div class="center bold" style="font-size: 16px;">ORCAS</div>

<div class="center bold" style="font-size: 14px;">Agent: <?php echo e($agentName); ?></div>

<div class="section" style="font-size: 13px;">
    <div>Draw Date: <strong><?php echo e($drawDate); ?></strong></div>
    <div>Txn Code: <strong>Stub-<?php echo e($stub); ?></strong></div>
</div>

<div class="divider"></div>

<table>
    <thead>
        <tr class="bold">
            <th style="text-align: left;">Draw</th>
            <th style="text-align: left;">Game</th>
            <th style="text-align: center;">Combi</th>
            <th style="text-align: right;">Bet</th>
        </tr>
    </thead>
    <tbody>
        <?php
            if (!function_exists('formatDrawTime')) {
                function formatDrawTime($draw) {
                    return match ((int) $draw) {
                        14 => '2PM',
                        17 => '5PM',
                        21 => '9PM',
                        default => $draw,
                    };
                }
            }
        ?>

        <?php $__currentLoopData = $bets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td style="text-align: left;"><?php echo e(formatDrawTime($bet->game_draw)); ?></td>
            <td style="text-align: left;"><?php echo e(strtoupper($bet->game_type)); ?></td>
            <td style="text-align: center;"><?php echo e($bet->bet_number); ?></td>
            <td style="text-align: right;"><?php echo e(number_format($bet->amount, 2)); ?></td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>

<div class="divider"></div>

<div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 14px; margin-top: 6px;">
    <div>TOTAL:</div>
    <div><?php echo e(number_format($totalAmount, 2)); ?></div>
</div>

<div class="divider"></div>

<div class="center section">
    <div style="font-size: 14px;">Stub #: <strong><?php echo e($bets->first()->id ?? 1); ?></strong></div>
    <div class="qr" style="margin-top: 6px;">
        <?php echo $qrCodeSvg; ?>

    </div>
    <div class="small">Stub-<?php echo e($stub); ?></div>
    <div class="small">Printed: <?php echo e($printedTime); ?></div>
</div>

<div style="height: 60px;"></div>

<script>
    window.print();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.thermalprinter', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/u355685815/domains/orcasbettingapp.com/resources/views/agent/prints/agent-receipt.blade.php ENDPATH**/ ?>