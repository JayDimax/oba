<!-- resources/views/cashier/receipt-compact.blade.php -->

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Receipt Stub: {{ $stub }}</title>
<style>
    /* 48mm ~ 180px width */
    body {
        width: 180px;
        font-family: monospace, monospace;
        font-size: 12px;
        margin: 0;
        padding: 5px;
        color: #000;
    }
    h1 {
        font-size: 14px;
        text-align: center;
        margin-bottom: 8px;
    }
    .line {
        border-top: 1px dashed #000;
        margin: 8px 0;
    }
    .item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 4px;
    }
    .item strong {
        font-weight: 700;
    }
    .total {
        margin-top: 8px;
        border-top: 1px solid #000;
        font-weight: 700;
        display: flex;
        justify-content: space-between;
        font-size: 14px;
        padding-top: 4px;
    }
    button.print-btn {
        display: none; /* hidden for thermal */
    }
</style>
</head>
<body>
    <h1>Receipt #{{ $stub }}</h1>

    @foreach ($bets as $bet)
    <div class="item">
        <div>
            <div><strong>{{ $bet->game_type }}</strong> Draw: {{ $bet->game_draw }}</div>
            <div>Number: {{ $bet->bet_number }}</div>
        </div>
        <div>₱{{ number_format($bet->amount, 2) }}</div>
    </div>
    @endforeach

    <div class="line"></div>

    <div class="total">
        <div>Total</div>
        <div>₱{{ number_format($totalAmount, 2) }}</div>
    </div>
</body>
</html>
