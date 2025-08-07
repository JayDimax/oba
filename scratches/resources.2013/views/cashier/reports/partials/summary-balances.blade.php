@php
    $totalBalance = $reports->sum('amount');
@endphp

<p><strong>Total Agent Balance:</strong> ₱{{ number_format($totalBalance, 2) }}</p>
