<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Remittance Batch #{{ $batch->id }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header, .footer { text-align: center; margin-bottom: 10px; }
        .section { margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #999; padding: 4px 6px; }
        th { background-color: #f0f0f0; }
        .subtotal, .overall-total { font-weight: bold; text-align: right; }
    </style>
</head>
<body>

<div class="header">
    <h2>Remittance Receipt</h2>
    <p>Batch ID: #{{ $batch->id }} | Date: {{ $batch->submitted_at->format('F d, Y h:i A') }}</p>
</div>

<div class="section">
    <strong>Cashier:</strong> {{ $batch->cashier->name }}<br>
    <strong>Agent:</strong> {{ $batch->agent->name }}<br>
    <strong>Status:</strong> {{ ucfirst($batch->status) }}
</div>

@foreach ($groupedBets as $group)
    <div class="section">
        <strong>Stub ID: {{ $group['stub_id'] }}</strong>
        <table>
            <thead>
                <tr>
                    <th>Game</th>
                    <th>Draw</th>
                    <th>Number</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($group['bets'] as $bet)
                    <tr>
                        <td>{{ $bet->game_type }}</td>
                        <td>{{ $bet->game_draw }}</td>
                        <td>{{ $bet->bet_number }}</td>
                        <td>₱{{ number_format($bet->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <p class="subtotal">Subtotal: ₱{{ number_format($group['total'], 2) }}</p>
    </div>
@endforeach

<hr>
<p class="overall-total">Total Remitted: ₱{{ number_format($totalOverall, 2) }}</p>

<div class="footer">
    Printed on {{ now()->format('F d, Y h:i A') }}
</div>

</body>
</html>
