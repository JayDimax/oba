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
        @if($selectedAgentId)
            {{ \App\Models\User::find($selectedAgentId)?->name ?? 'N/A' }}
        @else
            All Agents
        @endif
        )
    </h2>

    <!--<h2>Total Amount: ₱ {{ number_format($stubs->sum('total_amount'), 2) }}</h2>-->

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
            @foreach($stubs as $stub)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $stub->stub_id }}</td>
                    <td>₱{{ number_format($stub->total_amount, 2) }}</td>
                    <td>{{ $stub->latest_game_date }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="actions print:hidden">
        <a href="{{ route('admin.reports.index') }}" class="btn btn-back">← Back to Reports</a>
        <button onclick="window.print()" class="btn btn-print">🖨️ Print Report</button>
    </div>

</body>
</html>
