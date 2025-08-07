@if(request('print') === 'yes')
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
        @if(request()->anyFilled(['from_date', 'to_date', 'draw_time', 'agent_name']))
            <p><strong>Filters:</strong>
                @if(request('from_date')) From: {{ request('from_date') }} @endif
                @if(request('to_date')) To: {{ request('to_date') }} @endif
                @if(request('draw_time')) | Draw: {{ request('draw_time') }} @endif
                @if(request('agent_name')) | Agent: {{ request('agent_name') }} @endif
            </p>
        @endif

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
            @forelse($bets as $bet)
                <tr>
                    <td>{{ $bet->betAgent->agent_code ?? '—' }}</td>
                    <td>{{ \Carbon\Carbon::createFromTimeString($bet->game_draw)->format('g:i A') }}</td>
                    <td>{{ $bet->game_type }}</td>
                    <td>{{ $bet->bet_number }}</td>
                    <td>₱{{ number_format($bet->amount, 2) }}</td>
                    <td>{{ $bet->game_date ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center;">No bets found for this filter.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </body>
    </html>
@else
    {{-- Keep your existing full dashboard content here --}}
    {{-- Including filters, cards, and regular view --}}
@endif
