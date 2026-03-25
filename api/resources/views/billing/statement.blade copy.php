<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Mesečni obračun - {{ $unit->identifier }}</title>
    <style>
        @page {
            margin: 10mm;
            size: A4;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9pt;
            line-height: 1.2;
            margin-top: 15mm;
        }
        
        /* Gornji deo - obračun */
        .header {
            border-bottom: 1px solid #000;
            margin-bottom: 8px;
            padding-bottom: 3px;
        }
        .header h1 {
            margin: 0;
            font-size: 11pt;
            font-weight: bold;
        }
        .header .info {
            margin-top: 2px;
            font-size: 7pt;
        }
        
        .unit-info {
            border: 1px solid #ccc;
            padding: 5px;
            margin-bottom: 8px;
            font-size: 8pt;
        }
        .unit-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .unit-info td {
            padding: 1px 3px;
        }
        .unit-info .label {
            font-weight: bold;
            width: 18%;
        }
        
        .expenses-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 8pt;
        }
        .expenses-table th {
            background: #333;
            color: white;
            padding: 4px;
            text-align: left;
            border: 1px solid #000;
        }
        .expenses-table td {
            padding: 3px 4px;
            border: 1px solid #ccc;
        }
        .expenses-table .category-row {
            background: #e8e8e8;
            font-weight: bold;
        }
        .expenses-table .amount {
            text-align: right;
            font-family: 'DejaVu Sans Mono', monospace;
        }
        
        .total-row {
            background: #333;
            color: white;
            font-weight: bold;
            font-size: 10pt;
        }
        
        /* NALOG ZA UPLATU */
        .payment-slip {
            page-break-inside: avoid;
            border: 1px solid #000;
            margin-top: 12px;
            height: 80mm;
            position: relative;
            font-family: 'DejaVu Sans Mono', 'Courier New', monospace;
        }
        
        .slip-title {
            position: absolute;
            top: 5px;
            right: 10px;
            font-size: 12pt;
            font-weight: bold;
            letter-spacing: 2px;
        }
        
        .slip-body {
            display: table;
            width: 100%;
            height: 100%;
        }
        
        .slip-left {
            display: table-cell;
            width: 48%;
            padding: 30px 10px 10px 10px;
            vertical-align: top;
            height: 100%;
            position: relative;
        }
        
        .slip-right {
            display: table-cell;
            width: 52%;
            padding: 30px 10px 10px 10px;
            vertical-align: top;
            position: relative;
        }
        
        .field {
            margin-bottom: 6px;
            position: relative;
        }
        
        .field-label {
            font-size: 7pt;
            color: #333;
            margin-bottom: 2px;
            text-transform: lowercase;
            font-family: 'DejaVu Sans Mono', 'Courier New', monospace;
            white-space: nowrap;
        }
        
        .field-box {
            border: 1px solid #000;
            min-height: 18px;
            padding: 2px 5px;
            background: white;
            font-size: 9pt;
            font-family: 'DejaVu Sans Mono', 'Courier New', monospace;
        }
        
        .field-box.large {
            min-height: 22px;
        }
        
        .field-box.medium {
            min-height: 20px;
        }
        
        .field-underline {
            border-bottom: 1px solid #000;
            min-height: 18px;
            padding: 2px 0;
        }
        
        /* Right side grid */
        .right-row {
            display: table;
            width: 100%;
            margin-bottom: 6px;
        }
        
        .right-cell {
            display: table-cell;
            padding-right: 5px;
        }
        
        .right-cell.small {
            width: 25%;
        }
        
        .right-cell.medium {
            width: 30%;
        }
        
        .signature-label {
            font-size: 7pt;
            color: #333;
        }
        
        /* QR kod izvan uplatnice */
        .qr-section {
            margin: 15px 0;
            text-align: center;
            page-break-inside: avoid;
        }
        
        .qr-section svg {
            width: 200px;
            height: 200px;
            display: inline-block;
            border: 3px solid #000;
            background: #fff;
            padding: 10px;
        }
        
        .qr-label {
            font-size: 9pt;
            color: #000;
            margin-top: 5px;
            text-align: center;
            font-weight: bold;
        }
        
        /* Pečat i potpis */
        .signature-box {
            margin-top: 50px;
            border-top: 1px solid #000;
            padding-top: 3px;
            width: 80%;
        }
        
        /* Donji deo desne strane */
        .bottom-section {
            margin-top: 40px;
            bottom: 10px;
            left: 5px;
            right: 10px;
        }
        
        .bottom-row {
            display: table;
            width: 100%;
        }
        
        .bottom-cell {
            display: table-cell;
            width: 50%;
            padding-right: 8px;
        }
        
        .bottom-cell .field-underline {
            border-bottom: 1px solid #000;
            height: 18px;
            margin-bottom: 2px;
        }
        
        /* Vertikalna centralna linija */
        .slip-divider {
            position: absolute;
            top: 0;
            bottom: 0;
            left: 48%;
            width: 1px;
            background: #000;
        }
    </style>
</head>
<body>
    <!-- Gornji deo - Obračun troškova -->
    <div class="header">
        <h1>MESEČNI OBRAČUN TROŠKOVA ODRŽAVANJA - {{ strtoupper($period->translatedFormat('F Y')) }}</h1>
        <div class="info">
            {{ $community->name }} | {{ $community->address_line }}, {{ $community->city }} | PIB: {{ $community->tax_id }}
        </div>
    </div>

    <!-- Info i QR u istom redu -->
    <div style="display: table; width: 100%; margin-bottom: 12px;">
        <!-- Leva strana - Info (60%) -->
        <div style="display: table-cell; width: 60%; vertical-align: top; padding-right: 15px;">
            <div class="unit-info">
                <table>
                    <tr>
                        <td class="label">
                            @if($unit->type === 'stan')
                                Stan:
                            @elseif($unit->type === 'lokal')
                                Lokal:
                            @else
                                Stan/Lokal:
                            @endif
                        </td>
                        <td><strong>{{ $unit->identifier }}</strong></td>
                    </tr>
                    <tr>
                        <td class="label">Vlasnik:</td>
                        <td>
                            @if($owners->count() > 0)
                                {{ $owners->first()->full_name }}
                            @endif
                        </td>
                    </tr>
                    @if($unit->type === 'stan')
                    <tr>
                        <td class="label">Broj članova domaćinstva:</td>
                        <td><strong>{{ $unit->occupant_count }}</strong></td>
                    </tr>
                    @endif
                </table>
            </div>

            <table class="expenses-table">
                <tbody>
                    <tr class="total-row">
                        <td><strong>UKUPNO ZA UPLATU:</strong></td>
                        <td class="amount"><strong>{{ number_format($totalAmount, 2, ',', '.') }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Desna strana - QR kod (40%) -->
        <div style="display: table-cell; width: 40%; vertical-align: top; text-align: right;">
            @if($qrCode)
                <div style="display: inline-block; text-align: center;">
                    <div style="width: 200px; height: 200px;">
                        <img src="data:image/svg+xml;base64,{{ base64_encode($qrCode) }}" alt="QR Kod" style="width: 200px; height: 200px; display: block;">
                    </div>
                    <div class="qr-label">NBS IPS QR kod za plaćanje</div>
                </div>
            @else
                <div style="color: red; font-weight: bold;">QR KOD NEDOSTAJE!</div>
            @endif
        </div>
    </div>

    <!-- NALOG ZA UPLATU -->
    <div class="payment-slip">
        <div class="slip-title">NALOG ZA UPLATU</div>
        <div class="slip-divider"></div>
        
        <div class="slip-body">
            <!-- Leva strana -->
            <div class="slip-left">
                <div class="field">
                    <div class="field-label">uplatilac</div>
                    <div class="field-box medium">
                        @if($owners->count() > 0)
                            {{ strtoupper($owners->first()->full_name) }}
                        @endif
                    </div>
                </div>
                
                <div class="field">
                    <div class="field-label">svrha uplate</div>
                    <div class="field-box medium">
                        ODRZAVANJE ZGRADE ZA {{ strtoupper($period->translatedFormat('F Y')) }}
                    </div>
                </div>
                
                <div class="field">
                    <div class="field-label">primalac</div>
                    <div class="field-box large">
                        {{ strtoupper($community->name) }}
                    </div>
                </div>
                
                <!-- Pečat i potpis -->
                <div class="signature-box">
                    <div class="signature-label">pečat i potpis uplatioca</div>
                </div>
            </div>
            
            <!-- Desna strana -->
            <div class="slip-right">
                <div class="right-row">
                    <div class="right-cell small">
                        <div class="field-label">šifra plaćanja</div>
                        <div class="field-box">189</div>
                    </div>
                    <div class="right-cell small">
                        <div class="field-label">valuta</div>
                        <div class="field-box">RSD</div>
                    </div>
                    <div class="right-cell">
                        <div class="field-label">iznos</div>
                        <div class="field-box"><strong>={{ number_format($totalAmount, 2, ',', '.') }}</strong></div>
                    </div>
                </div>
                
                <div class="field">
                    <div class="field-label">račun primaoca</div>
                    <div class="field-box">{{ $community->bank_account_number }}</div>
                </div>
                
                <div class="right-row">
                    <div class="right-cell medium">
                        <div class="field-label">model</div>
                        <div class="field-box">97</div>
                    </div>
                    <div class="right-cell">
                        <div class="field-label">poziv na broj </div>
                        <div class="field-box"><strong>{{ $callNumber }}</strong></div>
                    </div>
                </div>
                
                <!-- Mesto, datum, valuta unutar uplatnice -->
                <div class="bottom-section">
                    <div class="bottom-row">
                        <div class="bottom-cell">
                            <div class="field-underline"></div>
                            <div class="field-label">mesto i datum prijema</div>
                        </div>
                        <div class="bottom-cell">
                            <div class="field-underline"></div>
                            <div class="field-label">datum valute</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
