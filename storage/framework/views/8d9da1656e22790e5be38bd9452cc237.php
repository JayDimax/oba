<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Sales Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            padding: 40px;
            background: #fff;
            color: #333;
        }

        h2, h3 {
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }

        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 30px;
        }

        .btn {
            display: inline-block;
            padding: 10px 16px;
            font-size: 14px;
            border-radius: 6px;
            text-decoration: none;
            transition: background-color 0.2s ease;
            border: none;
            cursor: pointer;
        }

        .btn-back {
            background-color: #e2e8f0;
            color: #1a202c;
        }

        .btn-back:hover {
            background-color: #cbd5e0;
        }

        .btn-print {
            background-color: #2563eb;
            color: #fff;
        }

        .btn-print:hover {
            background-color: #1d4ed8;
        }

        @media print {
            .actions {
                display: none;
            }

            body {
                padding: 0;
            }

            table {
                page-break-after: auto;
            }
        }
    </style>
</head>

<body>

    <h2>
        Sales Report of 
        (
        <?php if($selectedAgentId): ?>
            <?php echo e(\App\Models\User::find($selectedAgentId)?->name ?? 'N/A'); ?>

        <?php else: ?>
            All Agents
        <?php endif; ?>
        )
    </h2>

    <!--<h2>Total Amount: ₱ <?php echo e(number_format($stubs->sum('total_amount'), 2)); ?></h2>-->

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Stub ID</th>
                <th>Bet Amount</th>
                <th>Game Date</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $stubs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($loop->iteration); ?></td>
                    <td><?php echo e($stub->stub_id); ?></td>
                    <td>₱<?php echo e(number_format($stub->total_amount, 2)); ?></td>
                    <td><?php echo e($stub->latest_game_date); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>

    <div class="actions print:hidden">
        <a href="<?php echo e(route('admin.reports.index')); ?>" class="btn btn-back">← Back to Reports</a>
        <button onclick="window.print()" class="btn btn-print">🖨️ Print Report</button>
    </div>

</body>
</html>
<?php /**PATH /home/u355685815/domains/orcasbettingapp.com/resources/views/admin/reports/print.blade.php ENDPATH**/ ?>