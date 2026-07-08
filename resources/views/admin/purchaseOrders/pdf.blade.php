<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden de Compra {{ $purchaseOrder->code }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            padding: 15px;
            color: #333;
        }
        .header-container {
            width: 100%;
            margin-bottom: 15px;
            border-bottom: 2px solid #1a4a7a;
            padding-bottom: 12px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-cell {
            vertical-align: top;
        }
        .header-cell-left {
            width: 50%;
        }
        .header-cell-right {
            width: 50%;
            text-align: right;
        }
        .header-table td {
            border: none;
        }
        .logo-section {
            width: 150px;
        }
        .logo-section img {
            width: 130px;
            height: auto;
        }
        .supplier-header {
            font-size: 9px;
            margin-top: 10px;
        }
        .supplier-header-title {
            font-size: 9px;
            text-transform: uppercase;
            color: #1a4a7a;
            font-weight: bold;
            margin-bottom: 4px;
            border-bottom: 1px solid #1a4a7a;
            padding-bottom: 3px;
        }
        .supplier-header-item {
            margin-bottom: 2px;
            line-height: 1.3;
        }
        .company-info {
            text-align: right;
            margin-top: 0;
        }
        .company-name {
            font-size: 16px;
            font-weight: bold;
            color: #1a4a7a;
            margin-bottom: 3px;
        }
        .company-rif {
            font-size: 11px;
            color: #666;
        }
        .document-info {
            margin-top: 5px;
            text-align: right;
        }
        .document-title {
            font-size: 18px;
            font-weight: bold;
            color: #1a4a7a;
            margin-bottom: 5px;
        }
        .document-number {
            font-size: 12px;
            font-weight: bold;
        }
        .document-date {
            font-size: 11px;
            color: #666;
        }
        .info-section {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
        }
        .info-box {
            flex: 1;
            border: 1px solid #ddd;
            padding: 8px;
            background: #fafafa;
        }
        .info-box-title {
            font-size: 9px;
            text-transform: uppercase;
            color: #1a4a7a;
            font-weight: bold;
            margin-bottom: 5px;
            border-bottom: 1px solid #1a4a7a;
            padding-bottom: 3px;
        }
        .info-item {
            margin-bottom: 3px;
            line-height: 1.3;
        }
        .info-label {
            font-weight: bold;
            color: #555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            page-break-inside: auto;
        }
        tr {
            page-break-inside: avoid;
        }
        th, td {
            border: 1px solid #333;
            padding: 6px;
            text-align: left;
            line-height: 1.2;
        }
        th {
            background: #1a4a7a;
            color: white;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .totals-section {
            width: 100%;
            margin-bottom: 15px;
        }
        .supplier-totals-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .supplier-totals-cell {
            vertical-align: top;
        }
        .supplier-totals-left {
            width: 60%;
        }
        .supplier-totals-right {
            width: 40%;
        }
        .supplier-totals-table td {
            border: none;
        }
        .supplier-info-box {
            border: 1px solid #ddd;
            padding: 8px;
            background: #fafafa;
        }
        .supplier-info-title {
            font-size: 9px;
            text-transform: uppercase;
            color: #1a4a7a;
            font-weight: bold;
            margin-bottom: 5px;
            border-bottom: 1px solid #1a4a7a;
            padding-bottom: 3px;
        }
        .supplier-info-item {
            margin-bottom: 3px;
            line-height: 1.3;
            font-size: 9px;
        }
        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }
        .totals-table td {
            border: 1px solid #ddd;
            padding: 6px 10px;
        }
        .totals-label {
            font-weight: bold;
            text-align: right;
            background: #f5f5f5;
        }
        .totals-value {
            text-align: right;
        }
        .grand-total {
            background: #1a4a7a !important;
            color: white !important;
            font-size: 14px;
            font-weight: bold;
        }
        .terms-section {
            margin-bottom: 15px;
            page-break-inside: avoid;
        }
        .terms-title {
            font-size: 11px;
            font-weight: bold;
            color: #1a4a7a;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        .terms-content {
            border: 1px solid #ddd;
            padding: 12px;
            background: #fafafa;
            font-size: 10px;
            line-height: 1.5;
            white-space: pre-wrap;
        }
        .footer {
            margin-top: 30px;
            padding-top: 12px;
            border-top: 1px solid #ddd;
            font-size: 9px;
            color: #888;
            text-align: center;
            page-break-inside: avoid;
        }
        .signature-section {
            margin-top: 40px;
            width: 100%;
            page-break-inside: avoid;
        }
        .signature-row {
            display: table;
            width: 100%;
        }
        .signature-cell {
            display: table-cell;
            width: 50%;
            text-align: center;
            padding: 0 20px;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 40px;
            padding-top: 5px;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="header-container">
        <table class="header-table">
            <tr>
                <td class="header-cell header-cell-left">
                    <div class="logo-section">
                        <img src="{{ public_path('images/logo-iac.png') }}" style="width: 150px;" alt="Logo IAC">
                    </div>
                    <div class="supplier-header">
                        <div class="supplier-header-title">Datos del Proveedor</div>
                        <div class="supplier-header-item"><span style="font-weight: bold;">Nombre:</span> {{ $purchaseOrder->supplier->name }}</div>
                        @if($purchaseOrder->supplier->tax_id)
                            <div class="supplier-header-item"><span style="font-weight: bold;">RIF:</span> {{ $purchaseOrder->supplier->tax_id }}</div>
                        @endif
                        @if($purchaseOrder->supplier->contact_person)
                            <div class="supplier-header-item"><span style="font-weight: bold;">Contacto:</span> {{ $purchaseOrder->supplier->contact_person }}</div>
                        @endif
                        @if($purchaseOrder->supplier->phone)
                            <div class="supplier-header-item"><span style="font-weight: bold;">Tel:</span> {{ $purchaseOrder->supplier->phone }}</div>
                        @endif
                        @if($purchaseOrder->supplier->address)
                            <div class="supplier-header-item"><span style="font-weight: bold;">Dirección:</span> {{ $purchaseOrder->supplier->address }}</div>
                        @endif
                        @if($purchaseOrder->supplier->email)
                            <div class="supplier-header-item"><span style="font-weight: bold;">Email:</span> {{ $purchaseOrder->supplier->email }}</div>
                        @endif
                    </div>
                </td>
                <td class="header-cell header-cell-right">
                    <div class="company-info">
                        <div class="company-name">Inmunologia Asociacion Civil</div>
                        <div class="company-rif">RIF: J-30710739-1</div>
                        <div class="document-info">
                            <div class="document-title">ORDEN DE COMPRA</div>
                            <div class="document-number">N° {{ $purchaseOrder->code }}</div>
                            <div class="document-date">Fecha: {{ $purchaseOrder->date_issued->format('d/m/Y') }}</div>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <table style="width: 100%; table-layout: fixed;">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">#</th>
                <th style="width: 10%; text-align: left;">Código</th>
                <th style="width: 52%; text-align: left;">Descripción / Producto</th>
                <th style="width: 8%; text-align: center;">Cant.</th>
                <th style="width: 12%; text-align: right;">Costo Unit.</th>
                <th style="width: 5%; text-align: center;">Und.</th>
                <th style="width: 8%; text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchaseOrder->items as $index => $item)
                <tr>
                    <td style="text-align: center; vertical-align: middle;">{{ $index + 1 }}</td>
                    <td style="text-align: left; vertical-align: middle;">{{ $item->product_code ?? '-' }}</td>
                    <td style="text-align: left; vertical-align: middle;">{{ $item->product_name }}</td>
                    <td style="text-align: center; vertical-align: middle;">{{ $item->quantity }}</td>
                    <td style="text-align: right; vertical-align: middle;">{{ number_format($item->unit_cost, 2) }}</td>
                    <td style="text-align: center; vertical-align: middle;">{{ $item->item_type === 'kit' ? 'kit' : ($item->product->unit->abbreviation ?? 'und') }}</td>
                    <td style="text-align: right; vertical-align: middle;">{{ number_format($item->total_cost, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="supplier-totals-table">
        <tr>
            <td class="supplier-totals-cell supplier-totals-left">
                    <div class="supplier-info-box">
                    <div class="supplier-info-title">Información de la Orden</div>
                    <div class="supplier-info-item"><span style="font-weight: bold;">Fecha de Emisión:</span> {{ $purchaseOrder->date_issued->format('d/m/Y') }}</div>
                    <div class="supplier-info-item"><span style="font-weight: bold;">Fecha de Entrega:</span> {{ $purchaseOrder->delivery_date?->format('d/m/Y') ?? 'A convenir' }}</div>
                    <div class="supplier-info-item"><span style="font-weight: bold;">Lugar de Entrega:</span> {{ $purchaseOrder->delivery_address ?? 'Por definir' }}</div>
                    <div class="supplier-info-item"><span style="font-weight: bold;">Moneda:</span> {{ $purchaseOrder->currency }}</div>
                    {{--
                    <div class="supplier-info-item"><span style="font-weight: bold;">Tipo de Cambio:</span> {{ number_format($purchaseOrder->exchange_rate, 4) }}</div>
                    --}}
                    {{--
                    @if($purchaseOrder->iva_exempt)
                        <div class="supplier-info-item"><span style="font-weight: bold;">IVA:</span> Exento</div>
                    @endif
                    --}}
                    @if($purchaseOrder->creator)
                        <div class="supplier-info-item"><span style="font-weight: bold;">Elaborado por:</span> {{ $purchaseOrder->creator->name }}</div>
                    @endif
                </div>
            </td>
            <td class="supplier-totals-cell supplier-totals-right">
                <div class="totals-section">
                    <table class="totals-table">
                        @if($purchaseOrder->currency === 'Bs')
                        <tr>
                            <td class="totals-label">Subtotal Bs:</td>
                            <td class="totals-value">Bs {{ number_format($purchaseOrder->subtotal, 2) }}</td>
                        </tr>
                        {{--
                        @if(!$purchaseOrder->iva_exempt)
                        <tr>
                            <td class="totals-label" style="color: #dc3545;">IVA 16% Bs:</td>
                            <td class="totals-value" style="color: #dc3545;">Bs {{ number_format($purchaseOrder->tax_amount_bs, 2) }}</td>
                        </tr>
                        @else
                        <tr>
                            <td class="totals-label">IVA:</td>
                            <td class="totals-value">Exento</td>
                        </tr>
                        @endif
                        <tr class="grand-total">
                            <td class="totals-label" style="background: #1a4a7a; color: white;">TOTAL Bs:</td>
                            <td class="totals-value" style="background: #1a4a7a; color: white;">Bs {{ number_format($purchaseOrder->total_bs, 2) }}</td>
                        </tr>
                        --}}
                        @else
                        <tr>
                            <td class="totals-label">Subtotal ({{ $purchaseOrder->currency }}):</td>
                            <td class="totals-value">{{ $purchaseOrder->currency_symbol }}{{ number_format($purchaseOrder->subtotal, 2) }}</td>
                        </tr>
                        {{--
                        <tr>
                            <td class="totals-label">Equivalente Bs:</td>
                            <td class="totals-value">Bs {{ number_format($purchaseOrder->subtotal_bs, 2) }}</td>
                        </tr>
                        @if(!$purchaseOrder->iva_exempt)
                        <tr>
                            <td class="totals-label" style="color: #dc3545;">IVA 16% Bs:</td>
                            <td class="totals-value" style="color: #dc3545;">Bs {{ number_format($purchaseOrder->tax_amount_bs, 2) }}</td>
                        </tr>
                        @else
                        <tr>
                            <td class="totals-label">IVA:</td>
                            <td class="totals-value">Exento</td>
                        </tr>
                        @endif
                        <tr class="grand-total">
                            <td class="totals-label" style="background: #17a2b8; color: white;">TOTAL Bs:</td>
                            <td class="totals-value" style="background: #17a2b8; color: white;">Bs {{ number_format($purchaseOrder->total_bs, 2) }}</td>
                        </tr>
                        --}}
                        <tr>
                            <td class="totals-label" style="background: #1a4a7a; color: white;">Total ({{ $purchaseOrder->currency }}):</td>
                            <td class="totals-value" style="background: #1a4a7a; color: white;">{{ $purchaseOrder->currency_symbol }}{{ number_format($purchaseOrder->total, 2) }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </td>
        </tr>
    </table>

    {{--
    @if($purchaseOrder->iva_exempt)
    <div style="margin-bottom: 10px; padding: 5px; background: #d1ecf1; border: 1px solid #1a4a7a; font-size: 9px;">
        <strong>NOTA:</strong> Orden de compra exenta de IVA según normativa aplicable.
    </div>
    @endif
    --}}

    @if($purchaseOrder->terms)
        <div class="terms-section">
            <div class="terms-title">Términos y Condiciones</div>
            <div class="terms-content">{{ $purchaseOrder->terms }}</div>
        </div>
    @endif

    @php
        $displayNotes = $purchaseOrder->notes;
        if (preg_match('/^Generado desde RFQ-[^.]+?\.\s*(.*)$/i', $purchaseOrder->notes, $matches)) {
            $displayNotes = trim($matches[1]);
        }
    @endphp

    @if($displayNotes)
        <div class="terms-section">
            <div class="terms-title">Observaciones</div>
            <div class="terms-content">{{ $displayNotes }}</div>
        </div>
    @endif

    <div class="signature-section">
        <div class="signature-row">
            <div class="signature-cell">
                <div class="signature-line">Firma y Sello del Autorizador</div>
            </div>
            <div class="signature-cell">
                <div class="signature-line">Firma y Sello del Comprador</div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>Documento generado el {{ now()->format('d/m/Y') }} | Sistema de Gestión de Inventario - IAC</p>
    </div>
</body>
</html>
