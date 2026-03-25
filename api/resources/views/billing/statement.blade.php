<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Mesečni obračun - {{ $unit->identifier }}</title>
    <style>
        @page {
            margin: 15mm;
            size: A4;
        }
        
        * {
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #000;
            margin: 0;
            padding: 0;
        }
        
        /* ===== WRAPPER ZA POZICIONIRANJE ===== */
        /* A4 = 297mm, margine 15mm gore/dole = 267mm korisne površine */
        /* Presavijanje na 3: linije na ~89mm i ~178mm (od margine) */
        .page-wrapper {
            min-height: 267mm;
            position: relative;
        }
        
        /* ===== GORNJA TREĆINA (0-85mm) - Header + Info ===== */
        .top-section {
            height: 85mm;
            padding-bottom: 5mm;
        }
        
        /* ===== HEADER ===== */
        .header {
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }
        
        .header h1 {
            margin: 0 0 3px 0;
            font-size: 13pt;
            font-weight: bold;
        }
        
        .header .subtitle {
            font-size: 8pt;
        }
        
        .period-badge {
            display: inline-block;
            border: 2px solid #000;
            padding: 3px 10px;
            font-size: 9pt;
            margin-top: 6px;
            font-weight: bold;
        }
        
        /* ===== INFO KARTICE ===== */
        .info-card {
            border: 1px solid #000;
            padding: 10px;
            margin-bottom: 10px;
        }
        
        .info-card-title {
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
            border-bottom: 2px solid #000;
            padding-bottom: 4px;
        }
        
        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 4px;
        }
        
        .info-label {
            display: table-cell;
            width: 40%;
            font-size: 8pt;
        }
        
        .info-value {
            display: table-cell;
            font-weight: bold;
            font-size: 9pt;
        }
        
        .info-value.large {
            font-size: 11pt;
        }
        
        /* ===== SREDNJA TREĆINA (85mm-178mm) - QR + Troškovi ===== */
        .middle-section {
            height: 93mm;
            display: table;
            width: 100%;
            padding: 8mm 0;
        }
        
        .middle-left {
            display: table-cell;
            width: 55%;
            vertical-align: top;
            padding-right: 15px;
        }
        
        .middle-right {
            display: table-cell;
            width: 45%;
            vertical-align: middle;
        }
        
        /* ===== TABELA TROŠKOVA ===== */
        .expenses-section {
            margin-bottom: 10px;
        }
        
        .section-title {
            font-size: 9pt;
            font-weight: bold;
            border-bottom: 2px solid #000;
            padding-bottom: 4px;
            margin-bottom: 8px;
        }
        
        .expense-item {
            display: table;
            width: 100%;
            padding: 5px 0;
            border-bottom: 1px solid #000;
        }
        
        .expense-item:last-child {
            border-bottom: none;
        }
        
        .expense-name {
            display: table-cell;
            font-size: 9pt;
        }
        
        .expense-amount {
            display: table-cell;
            text-align: right;
            font-family: 'DejaVu Sans Mono', monospace;
            font-size: 9pt;
        }
        
        .category-header {
            border: 1px solid #000;
            padding: 4px 8px;
            font-weight: bold;
            font-size: 8pt;
            margin: 8px 0 4px 0;
        }
        
        /* ===== TOTAL BOX ===== */
        .total-box {
            border: 2px solid #000;
            padding: 8px 12px;
            margin-top: 10px;
            background: #f5f5f5;
        }
        
        .total-label {
            font-size: 8pt;
            font-weight: bold;
            margin-bottom: 2px;
        }
        
        .total-amount {
            font-size: 16pt;
            font-weight: bold;
            font-family: 'DejaVu Sans Mono', monospace;
        }
        
        .total-currency {
            font-size: 10pt;
        }
        
        /* ===== QR SEKCIJA ===== */
        .qr-card {
            padding: 10px 0;
            text-align: center;
            background: #fff;
        }
        
        .qr-code-wrapper {
            display: inline-block;
            padding: 0;
        }
        
        .qr-code-wrapper img {
            width: 200px;
            height: 200px;
            display: block;
        }
        
        .qr-footer {
            text-align: center;
            margin-top: 5px;
        }
        
        .qr-instructions {
            font-size: 7pt;
            line-height: 1.3;
            margin-bottom: 3px;
        }
        
        .qr-badge {
            display: inline-block;
            border: 1px solid #000;
            padding: 2px 8px;
            font-size: 7pt;
            font-weight: bold;
        }
        
        /* ===== DONJA TREĆINA - NALOG ZA UPLATU ===== */
        .payment-slip-wrapper {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
        }
        
        .payment-slip {
            page-break-inside: avoid;
            border: 1px solid #000;
            height: 75mm;
            position: relative;
            font-family: 'DejaVu Sans', sans-serif;
            background: white;
        }
        
        .slip-title {
            position: absolute;
            top: 5px;
            right: 10px;
            font-size: 11pt;
            font-weight: bold;
            letter-spacing: 2px;
            color: #000;
        }
        
        .slip-body {
            display: table;
            width: 100%;
            height: 100%;
        }
        
        .slip-left {
            display: table-cell;
            width: 34%;
            padding: 30px 8px 10px 10px;
            vertical-align: top;
            height: 100%;
            position: relative;
        }
        
        .slip-middle {
            display: table-cell;
            width: 33%;
            padding: 30px 8px 10px 8px;
            vertical-align: top;
            position: relative;
        }
        
        .slip-right {
            display: table-cell;
            width: 33%;
            padding: 30px 10px 10px 8px;
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
            font-family: 'DejaVu Sans', sans-serif;
            white-space: nowrap;
        }
        
        .field-box {
            border: 1px solid #000;
            min-height: 16px;
            padding: 2px 5px;
            background: white;
            font-size: 8pt;
            font-family: 'DejaVu Sans', sans-serif;
        }
        
        .field-box.large {
            min-height: 22px;
        }
        
        .field-box.medium {
            min-height: 20px;
        }
        
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
            width: 28%;
        }
        
        .right-cell.medium {
            width: 35%;
        }
        
        .right-cell.wide {
            width: 72%;
        }
        
        .signature-box {
            margin-top: 20px;
            border-top: 1px solid #000;
            width: 80%;
        }
        
        .signature-label {
            font-size: 7pt;
            color: #333;
        }
        
        .slip-platilac {
            margin-top: 6px;
        }
        
        .slip-platilac .field-box {
            min-height: 24px;
            font-size: 7pt;
        }
        
        .bottom-section {
            margin-top: 42px;
            bottom: 10px;
            left: 0;
            right: 0;
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
        
        .slip-divider {
            position: absolute;
            top: 13mm;
            bottom: 13mm;
            left: 34%;
            width: 1px;
            background: #000;
        }
        
        .slip-divider-2 {
            position: absolute;
            top: 13mm;
            bottom: 13mm;
            left: 67%;
            width: 1px;
            background: #000;
        }
        
      
    </style>
</head>
<body>
    <div class="page-wrapper">
        <!-- Oznake za presavijanje (opciono, možeš ukloniti) -->
       
        
        <!-- ===== GORNJA TREĆINA: Header + Info o jedinici ===== -->
        <div class="top-section">
            <div class="header">
                <h1>{{ $community->name }}</h1>
                <div class="subtitle">{{ $community->address_line }}, {{ $community->city }} | PIB: {{ $community->tax_id }}</div>
                <div class="period-badge">OBRAČUN ZA {{ mb_strtoupper($period->translatedFormat('F Y')) }}</div>
            </div>
            
            <div class="info-card-none">
                <div class="info-card-title">Podaci o jedinici</div>
                <div class="info-row">
                    <div class="info-label">
                        @if($unit->type === 'stan')
                            Stambena jedinica:
                        @elseif($unit->type === 'lokal')
                            Poslovni prostor:
                        @else
                            Jedinica:
                        @endif
                    </div>
                    <div class="info-value large">{{ $unit->identifier }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Vlasnik:</div>
                    <div class="info-value large">
                        @if($owners->count() > 0)
                            {{ $owners->first()->full_name }}
                        @else
                            -
                        @endif
                    </div>
                </div>
                @if($unit->area)
                <div class="info-row">
                    <div class="info-label">Površina:</div>
                    <div class="info-value">{{ number_format($unit->area, 2, ',', '.') }} m²</div>
                </div>
                @endif
                @if($unit->type === 'stan' && $unit->occupant_count)
                <div class="info-row">
                    <div class="info-label">Članova domaćinstva:</div>
                    <div class="info-value">{{ $unit->occupant_count }}</div>
                </div>
                @endif
            </div>
        </div>
        
        <!-- ===== SREDNJA TREĆINA: Troškovi + QR kod ===== -->
        <div class="middle-section">
            <div class="middle-left">
                <div class="expenses-section">
                    <div class="info-card-title">Pregled troškova</div>
                    
                    <div class="expense-item">
                        <div class="expense-name">Naknada za održavanje</div>
                        <div class="expense-amount">{{ number_format($unitFee, 2, ',', '.') }} RSD</div>
                    </div>
                    
                    @if(count($expenses) > 0)
                        @foreach($expenses as $category => $categoryExpenses)
                            <div class="category-header">{{ $category }}</div>
                            @foreach($categoryExpenses as $expense)
                                <div class="expense-item">
                                    <div class="expense-name">{{ $expense['description'] }}</div>
                                    <div class="expense-amount">{{ number_format($expense['amount'], 2, ',', '.') }} RSD</div>
                                </div>
                            @endforeach
                        @endforeach
                    @endif
                </div>
                
            </div>
            
            <div class="middle-right">
                <div class="qr-card">
                    @if($qrCode)
                        <div class="qr-code-wrapper">
                            <img src="data:image/svg+xml;base64,{{ base64_encode($qrCode) }}" alt="QR Kod">
                        </div>
                        <div class="qr-footer">
                            <div class="qr-instructions">
                                Skenirajte QR kod mobilnom<br>
                                aplikacijom vaše banke
                            </div>
                            <div class="qr-badge">NBS IPS QR</div>
                        </div>
                    @else
                        <div style="color: #e74c3c; padding: 30px;">
                            QR kod nije dostupan
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- ===== DONJA TREĆINA: Nalog za uplatu ===== -->
        <div class="payment-slip-wrapper">
            <div class="payment-slip">
                <div class="slip-title">NALOG ZA UPLATU</div>
                <div class="slip-divider"></div>
                <div class="slip-divider-2"></div>
                
                <div class="slip-body">
                    <!-- ===== LEVA KOLONA: Uplatilac, Svrha, Primalac ===== -->
                    <div class="slip-left">
                        <div class="field">
                            <div class="field-label">uplatilac</div>
                            <div class="field-box medium">
                                @if($owners->count() > 0)
                                    {{ mb_strtoupper($owners->first()->full_name) }}@if($owners->first()->address), {{ mb_strtoupper($owners->first()->address) }}@endif
                                @endif
                            </div>
                        </div>
                        
                        <div class="field">
                            <div class="field-label">svrha uplate</div>
                            <div class="field-box medium">
                                ODRŽAVANJE ZGRADE ZA {{ mb_strtoupper($period->translatedFormat('F Y')) }}
                            </div>
                        </div>
                        
                        <div class="field">
                            <div class="field-label">primalac</div>
                            <div class="field-box large">
                                {{ mb_strtoupper($community->name) }}
                            </div>
                        </div>
                        
                        <div class="signature-box">
                            <div class="signature-label">pečat i potpis uplatioca</div>
                        </div>
                    </div>
                    
                    <!-- ===== SREDNJA KOLONA: Šifra, Valuta, Iznos, Račun, Model, Poziv ===== -->
                    <div class="slip-middle">
                        <div class="right-row">
                            <div class="right-cell small">
                                <div class="field-label">šif. plać.</div>
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
                            <div class="field-label">tekući račun primaoca</div>
                            <div class="field-box">{{ $community->bank_account_number }}</div>
                        </div>
                        
                        <div class="right-row">
                            <div class="right-cell medium">
                                <div class="field-label">broj modela</div>
                                <div class="field-box">97</div>
                            </div>
                            <div class="right-cell">
                                <div class="field-label">poziv na broj</div>
                                <div class="field-box"><strong>{{ substr($reference, 2) }}</strong></div>
                            </div>
                        </div>
                        
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
                    
                    <!-- ===== DESNA KOLONA: Kopija srednje + Platilac na dnu ===== -->
                    <div class="slip-right">
                        <div class="right-row">
                            <div class="right-cell small">
                                <div class="field-label">šif. plać.</div>
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
                            <div class="field-label">tekući račun primaoca</div>
                            <div class="field-box">{{ $community->bank_account_number }}</div>
                        </div>
                        
                        <div class="right-row">
                            <div class="right-cell medium">
                                <div class="field-label">broj modela</div>
                                <div class="field-box">97</div>
                            </div>
                            <div class="right-cell">
                                <div class="field-label">poziv na broj</div>
                                <div class="field-box"><strong>{{ substr($reference, 2) }}</strong></div>
                            </div>
                        </div>
                        
                        <div class="slip-platilac">
                            <div class="field-label">platilac</div>
                            <div class="field-box">
                                @if($owners->count() > 0)
                                    {{ mb_strtoupper($owners->first()->full_name) }}@if($owners->first()->address)<br>{{ mb_strtoupper($owners->first()->address) }}@endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
