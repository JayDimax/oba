<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $formattedTitle }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
        th { background-color: #f2f2f2; }
        @media print {
            button { display: none; }
        }
    </style>
</head>
<body>
    <h2 class="text-lg font-bold text-center">{{ $formattedTitle }}</h2>

    <table>
        <thead>
            <tr>
                <th>Agent</th>
                <th>Gross Sales</th>
                <th>Net Sales</th>
                <th>Net Remittance</th>
                <th>Balance</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $row)
                <tr>
                    <td>{{ $row['agent']->name ?? 'N/A' }}</td>
                    <td>{{ number_format($row['gross_sales'], 2) }}</td>
                    <td>{{ number_format($row['net_sales'], 2) }}</td>
                    <td>{{ number_format($row['net_remittance'], 2) }}</td>
                    <td>{{ number_format($row['difference'], 2) }}</td>
                    <td>{{ $row['status'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 30px;">
        <button onclick="window.print()">Print Report</button>
    </div>
</body>
</html>
