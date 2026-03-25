<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <title>Izvod - {{ $unit->identifier }} - {{ $statement->period }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
        }
        .header p {
            margin: 5px 0 0;
            color: #666;
        }
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .info-row {
            display: table-row;
        }
        .info-cell {
            display: table-cell;
            padding: 5px 10px;
            border: 1px solid #ddd;
        }
        .info-label {
            background: #f5f5f5;
            font-weight: bold;
            width: 150px;
        }
        .statement-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .statement-table th,
        .statement-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .statement-table th {
            background: #f5f5f5;
        }
        .statement-table .amount {
            text-align: right;
            font-family: monospace;
        }
        .total-row {
            font-weight: bold;
            background: #f0f0f0;
        }
        .positive { color: #c00; }
        .negative { color: #060; }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #666;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>MESEČNI IZVOD</h1>
        <p>{{ $community->name }}</p>
        <p>{{ $community->address_line }}, {{ $community->city }}</p>
    </div>

    <div class="info-grid">
        <div class="info-row">
            <div class="info-cell info-label">Period:</div>
            <div class="info-cell">{{ $statement->period_label }}</div>
        </div>
        <div class="info-row">
            <div class="info-cell info-label">Stan:</div>
            <div class="info-cell">{{ $unit->identifier }}</div>
        </div>
        <div class="info-row">
            <div class="info-cell info-label">Površina:</div>
            <div class="info-cell">{{ number_format($unit->area, 2, ',', '.') }} m²</div>
        </div>
        <div class="info-row">
            <div class="info-cell info-label">Datum izvoda:</div>
            <div class="info-cell">{{ $statement->generated_at->format('d.m.Y') }}</div>
        </div>
    </div>

    <table class="statement-table">
        <thead>
            <tr>
                <th>Opis</th>
                <th class="amount">Iznos (RSD)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Prethodni saldo (prenos)</td>
                <td class="amount {{ $statement->opening_balance > 0 ? 'positive' : 'negative' }}">
                    {{ number_format($statement->opening_balance, 2, ',', '.') }}
                </td>
            </tr>
            <tr>
                <td>Zaduženja u periodu</td>
                <td class="amount positive">
                    +{{ number_format($statement->charges, 2, ',', '.') }}
                </td>
            </tr>
            <tr>
                <td>Uplate u periodu</td>
                <td class="amount negative">
                    -{{ number_format($statement->payments, 2, ',', '.') }}
                </td>
            </tr>
            <tr class="total-row">
                <td>SALDO ZA UPLATU</td>
                <td class="amount {{ $statement->closing_balance > 0 ? 'positive' : 'negative' }}">
                    {{ number_format($statement->closing_balance, 2, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>

    @if($statement->closing_balance > 0)
        <p style="margin-top: 20px; padding: 10px; background: #fff3cd; border: 1px solid #ffc107;">
            <strong>Napomena:</strong> Molimo vas da izmirite dugovanje u iznosu od 
            <strong>{{ number_format($statement->closing_balance, 2, ',', '.') }} RSD</strong>.
        </p>
    @endif

    <div class="footer">
        <p>Dokument generisan: {{ now()->format('d.m.Y H:i') }}</p>
        <p>{{ $community->name }} | PIB: {{ $community->tax_id ?? 'N/A' }}</p>
    </div>
</body>
</html>
