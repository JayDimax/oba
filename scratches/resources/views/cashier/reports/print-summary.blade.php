<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $formattedTitle ?? 'Collection Summary Report' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            padding: 20px;
            color: #000;
        }
        h2 {
            text-align: center;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #999;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
        @media print {
            button {
                display: none;
            }
        }
    </style>
</head>
<body>

    <h2>{{ $formattedTitle ?? 'Collection Summary Report' }}</h2>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Agent</th>
                <th>Collection Date</th>
                <th>Gross (₱)</th>
                <!-- <th>Payouts (₱)</th>
                <th>Deductions (₱)</th> -->
                <th>Net Remit (₱)</th>
                <th>Reference</th>
                <th>Status</th>
                <th>Verified At</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($reports as $i => $collection)
                <tr>
                    <td>{{ $reports->firstItem() + $i }}</td>
                    <td>{{ $collection->agent?->name ?? 'Unknown' }}</td>
                    <td>{{ \Carbon\Carbon::parse($collection->collection_date)->format('M d, Y') }}</td>
                    <td>{{ number_format($collection->gross ?? 0, 2) }}</td>
                    <!-- <td>{{ number_format($collection->payouts ?? 0, 2) }}</td>
                    <td>{{ number_format($collection->deductions ?? 0, 2) }}</td> -->
                    <td>{{ number_format($collection->net_remit ?? 0, 2) }}</td>
                    <td>{{ $collection->gcash_reference ?? '-' }}</td>
                    <td>{{ ucfirst($collection->status ?? 'N/A') }}</td>
                    <td>{{ $collection->verified_at ? \Carbon\Carbon::parse($collection->verified_at)->format('M d, Y h:i A') : '-' }}</td>
                    <td>{{ $collection->remarks ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11">No collections found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 30px; text-align: center;">
        <button onclick="window.print()">🖨️ Print Report</button>
    </div>

</body>
</html>
