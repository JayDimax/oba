

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
</style>

<div class="center" style="margin-bottom: 6px;">
    <img src="<?php echo e(asset('images/tlogo.png')); ?>" alt="Logo"
         style="width: 80px; max-width: 100%; display: inline-block;">
</div>

<div class="center bold" style="font-size: 16px;">ORCAS</div>
<div class="center bold" style="font-size: 14px;">Agent: <?php echo e($bets->first()->betAgent->name ?? 'N/A'); ?></div>

<div class="section" style="font-size: 13px;">
    <div>Draw Date: <strong><?php echo e($bets->first()->game_date); ?></strong></div>

    <?php
        $stubList = $bets->pluck('stub_id')->unique();
        $chunks = $stubList->chunk(3); // Fix: properly chunk the unique stub IDs
    ?>

    <div>
        Txn Code(s):
        <strong>
            <?php $__currentLoopData = $chunks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chunk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php echo e($chunk->implode(', ')); ?><br>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </strong>
    </div>
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
        <?php $totalAmount = 0; ?>
        <?php $__currentLoopData = $bets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td style="text-align: left;">
                    <?php echo e(match ((int) $bet->game_draw) {
                        14 => '2PM',
                        17 => '5PM',
                        21 => '9PM',
                        default => $bet->game_draw,
                    }); ?>

                </td>
                <td style="text-align: left;"><?php echo e(strtoupper($bet->game_type)); ?></td>
                <td style="text-align: center;"><?php echo e($bet->bet_number); ?></td>
                <td style="text-align: right;"><?php echo e(number_format($bet->amount, 2)); ?></td>
            </tr>
            <?php $totalAmount += $bet->amount; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>

<div class="divider"></div>

<div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 18px; margin-top: 6px;">
    <div>TOTAL:</div>
    <div><?php echo e(number_format($totalAmount, 2)); ?></div>
</div>

<div class="divider"></div>


<?php if($stubList->count() === 1): ?>
    <div class="center section">
        <?php echo QrCode::size(50)->generate($stubList->first()); ?>

    </div>
<?php endif; ?>

<div class="center section small">
    Printed: <?php echo e(now()->format('Y-m-d H:i:s')); ?>

</div>

<div style="height: 60px;"></div>

<script>
    window.print();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.thermalprinter', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/u355685815/domains/orcasbettingapp.com/resources/views/agent/prints/multi.blade.php ENDPATH**/ ?>