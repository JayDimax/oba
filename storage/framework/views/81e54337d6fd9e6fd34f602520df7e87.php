<?php if(request('print') === 'yes'): ?>
    <html>
    <head>
        <title>Print Bets Report</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                font-size: 12px;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
            }

            th, td {
                border: 1px solid #000;
                padding: 6px 10px;
                text-align: left;
            }

            th {
                background-color: #f0f0f0;
            }

            @media print {
                @page { margin: 20mm }
                body { margin: 0; }
                .no-print { display: none !important; }
            }
        </style>
    </head>
    <body onload="window.print();">
        <h2 style="text-align:center;">Betting Report</h2>
        <?php if(request()->anyFilled(['from_date', 'to_date', 'draw_time', 'agent_name'])): ?>
            <p><strong>Filters:</strong>
                <?php if(request('from_date')): ?> From: <?php echo e(request('from_date')); ?> <?php endif; ?>
                <?php if(request('to_date')): ?> To: <?php echo e(request('to_date')); ?> <?php endif; ?>
                <?php if(request('draw_time')): ?> | Draw: <?php echo e(request('draw_time')); ?> <?php endif; ?>
                <?php if(request('agent_name')): ?> | Agent: <?php echo e(request('agent_name')); ?> <?php endif; ?>
            </p>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>Agent Code</th>
                    <th>Game Time</th>
                    <th>Game Type</th>
                    <th>Bet #</th>
                    <th>Bet Amount</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $bets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($bet->betAgent->agent_code ?? '—'); ?></td>
                    <td><?php echo e(\Carbon\Carbon::createFromTimeString($bet->game_draw)->format('g:i A')); ?></td>
                    <td><?php echo e($bet->game_type); ?></td>
                    <td><?php echo e($bet->bet_number); ?></td>
                    <td>₱<?php echo e(number_format($bet->amount, 2)); ?></td>
                    <td><?php echo e($bet->game_date ?? '—'); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="6" style="text-align:center;">No bets found for this filter.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </body>
    </html>
<?php else: ?>
    
    
<?php endif; ?>
<?php /**PATH /home/u355685815/domains/orcasbettingapp.com/resources/views/admin/bets-print.blade.php ENDPATH**/ ?>