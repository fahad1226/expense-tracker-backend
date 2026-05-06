<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Expense report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; }
        h1 { font-size: 18px; margin: 0 0 8px 0; }
        .muted { color: #6b7280; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #e5e7eb; padding: 6px 8px; text-align: left; }
        th { background: #f9fafb; font-weight: 600; font-size: 10px; text-transform: uppercase; }
        .num { text-align: right; }
        section { margin-top: 20px; page-break-inside: avoid; }
        .kpis { width: 100%; margin-top: 10px; }
        .kpis td { border: none; padding: 4px 8px 4px 0; width: 33%; }
        .kpi-val { font-size: 14px; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Expense report</h1>
    <p class="muted">{{ $data['userName'] }} · Generated {{ $data['generatedAt'] }}</p>
    <p><strong>Period:</strong> {{ $data['period']['start'] }} — {{ $data['period']['end'] }}</p>

    <section>
        <h2 style="font-size: 13px; margin-bottom: 6px;">Summary</h2>
        <table class="kpis">
            <tr>
                <td>
                    <div class="muted">Total spend</div>
                    <div class="kpi-val">${{ number_format($data['summary']['totalSpend'], 2) }}</div>
                </td>
                <td>
                    <div class="muted">Avg / day</div>
                    <div class="kpi-val">${{ number_format($data['summary']['avgPerDay'], 2) }}</div>
                </td>
                <td>
                    <div class="muted">Transactions</div>
                    <div class="kpi-val">{{ $data['summary']['transactionCount'] }}</div>
                </td>
            </tr>
        </table>
        @if($data['summary']['topCategory'])
            <p style="margin-top:8px;"><strong>Top category:</strong> {{ $data['summary']['topCategory']['name'] }}
                ({{ number_format($data['summary']['topCategory']['sharePercent'], 1) }}%)</p>
        @endif
    </section>

    @if($data['includeCompare'] && $data['comparePeriod'] && $data['summary']['deltas'])
        <section>
            <h2 style="font-size: 13px;">Prior period</h2>
            <p class="muted">{{ $data['comparePeriod']['start'] }} — {{ $data['comparePeriod']['end'] }}</p>
            <table>
                <tr>
                    <th>Metric</th>
                    <th class="num">Change %</th>
                </tr>
                @php $d = $data['summary']['deltas']; @endphp
                <tr>
                    <td>Total spend</td>
                    <td class="num">{{ $d['totalSpendPercent'] ?? '—' }}</td>
                </tr>
                <tr>
                    <td>Avg / day</td>
                    <td class="num">{{ $d['avgPerDayPercent'] ?? '—' }}</td>
                </tr>
                <tr>
                    <td>Transactions</td>
                    <td class="num">{{ $d['transactionCountPercent'] ?? '—' }}</td>
                </tr>
            </table>
        </section>
    @endif

    @if($data['includeCategory'] && count($data['categoryMix']) > 0)
        <section>
            <h2 style="font-size: 13px;">Category breakdown</h2>
            <table>
                <tr>
                    <th>Category</th>
                    <th class="num">Amount</th>
                    <th class="num">Share %</th>
                </tr>
                @foreach($data['categoryMix'] as $row)
                    <tr>
                        <td>{{ $row['name'] }}</td>
                        <td class="num">${{ number_format($row['amount'], 2) }}</td>
                        <td class="num">{{ number_format($row['sharePercent'], 1) }}</td>
                    </tr>
                @endforeach
            </table>
        </section>
    @endif

    @if($data['includeTransactions'] && count($data['transactions']) > 0)
        <section>
            <h2 style="font-size: 13px;">Transactions</h2>
            <table>
                <tr>
                    <th>Date</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th class="num">Amount</th>
                </tr>
                @foreach($data['transactions'] as $t)
                    <tr>
                        <td>{{ $t['date'] }}</td>
                        <td>{{ $t['category'] }}</td>
                        <td>{{ $t['description'] }}</td>
                        <td class="num">${{ number_format($t['amount'], 2) }}</td>
                    </tr>
                @endforeach
            </table>
        </section>
    @endif
</body>
</html>
