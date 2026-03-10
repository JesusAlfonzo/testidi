<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cotización {{ $quotation->code }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            padding: 20px;
            color: #333;
        }
        .header-container {
            width: 100%;
            margin-bottom: 20px;
            border-bottom: 2px solid #1a4a7a;
            padding-bottom: 15px;
        }
        .header-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .logo-section {
            width: 180px;
        }
        .logo-section img {
            width: 150px;
            height: auto;
        }
        .company-info {
            text-align: right;
            flex: 1;
            padding-left: 20px;
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
            margin-top: 15px;
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
            gap: 30px;
            margin-bottom: 20px;
        }
        .info-box {
            flex: 1;
            border: 1px solid #ddd;
            padding: 12px;
            background: #fafafa;
        }
        .info-box-title {
            font-size: 10px;
            text-transform: uppercase;
            color: #1a4a7a;
            font-weight: bold;
            margin-bottom: 8px;
            border-bottom: 1px solid #1a4a7a;
            padding-bottom: 5px;
        }
        .info-item {
            margin-bottom: 4px;
            line-height: 1.4;
        }
        .info-label {
            font-weight: bold;
            color: #555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
        }
        th {
            background: #1a4a7a;
            color: white;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .totals-section {
            width: 300px;
            margin-left: auto;
            margin-bottom: 20px;
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
        .notes-section {
            margin-bottom: 20px;
        }
        .notes-title {
            font-size: 11px;
            font-weight: bold;
            color: #1a4a7a;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        .notes-content {
            border: 1px solid #ddd;
            padding: 12px;
            background: #fafafa;
            font-size: 10px;
            line-height: 1.5;
        }
        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            font-size: 9px;
            color: #888;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header-container">
        <div class="header-row">
            <div class="logo-section">
                <img src="{{ public_path('images/logo-iac.png') }}" alt="Logo IAC">
            </div>
            <div class="company-info">
                <div class="company-name">Inmunologia Asociacion Civil</div>
                <div class="company-rif">RIF: J-30710739-1</div>
                <div class="document-info">
                    <div class="document-title">COTIZACIÓN</div>
                    <div class="document-number">N° {{ $quotation->code }}</div>
                    <div class="document-date">Fecha: {{ $quotation->date_issued->format('d/m/Y') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="info-section">
        <div class="info-box">
            <div class="info-box-title">Datos del Proveedor</div>
            <div class="info-item"><span class="info-label">Nombre:</span> {{ $quotation->getSupplierDisplayName() }}</div>
            @if($quotation->supplier && $quotation->supplier->tax_id)
                <div class="info-item"><span class="info-label">RIF:</span> {{ $quotation->supplier->tax_id }}</div>
            @endif
            @if($quotation->getSupplierDisplayEmail() != '-')
                <div class="info-item"><span class="info-label">Email:</span> {{ $quotation->getSupplierDisplayEmail() }}</div>
            @endif
            @if($quotation->supplier_phone_temp || ($quotation->supplier && $quotation->supplier->phone))
                <div class="info-item"><span class="info-label">Teléfono:</span> {{ $quotation->supplier_phone_temp ?? $quotation->supplier->phone }}</div>
            @endif
            @if($quotation->supplier_reference)
                <div class="info-item"><span class="info-label">Ref. Proveedor:</span> {{ $quotation->supplier_reference }}</div>
            @endif
        </div>
        <div class="info-box">
            <div class="info-box-title">Información de la Cotización</div>
            @if($quotation->rfq)
                <div class="info-item"><span class="info-label">RFQ:</span> {{ $quotation->rfq->code }}</div>
            @endif
            <div class="info-item"><span class="info-label">Fecha de Emisión:</span> {{ $quotation->date_issued->format('d/m/Y') }}</div>
            <div class="info-item"><span class="info-label">Válido hasta:</span> {{ $quotation->valid_until?->format('d/m/Y') ?? 'Sin fecha límite' }}</div>
            <div class="info-item"><span class="info-label">Fecha de Entrega:</span> {{ $quotation->delivery_date?->format('d/m/Y') ?? 'A convenir' }}</div>
            <div class="info-item"><span class="info-label">Moneda:</span> {{ $quotation->currency }}</div>
            <div class="info-item"><span class="info-label">Tipo de Cambio:</span> {{ number_format($quotation->exchange_rate, 4) }}</div>
            @if($quotation->user)
                <div class="info-item"><span class="info-label">Elaborado por:</span> {{ $quotation->user->name }}</div>
            @endif
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">#</th>
                <th style="width: 10%;">Código</th>
                <th style="width: 25%;">Descripción / Producto</th>
                <th style="width: 8%; text-align: center;">Cant.</th>
                <th style="width: 12%; text-align: right;">Costo Unit.</th>
                @if($quotation->is_foreign_currency)
                <th style="width: 12%; text-align: right;">Equiv. Bs</th>
                @endif
                <th style="width: 10%; text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quotation->items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->product->code ?? '-' }}</td>
                    <td>{{ $item->product_name }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">{{ $quotation->currency_symbol }}{{ number_format($item->unit_cost, 2) }}</td>
                    @if($quotation->is_foreign_currency)
                    <td class="text-right">Bs {{ number_format($item->equivalent_bs / $item->quantity, 2) }}</td>
                    @endif
                    <td class="text-right">{{ $quotation->currency_symbol }}{{ number_format($item->total_cost, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals-section">
        <table class="totals-table">
            @if($quotation->currency === 'Bs')
            <tr>
                <td class="totals-label">Subtotal Bs (sin IVA):</td>
                <td class="totals-value">Bs {{ number_format($quotation->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td class="totals-label" style="color: #dc3545;">IVA 16% Bs:</td>
                <td class="totals-value" style="color: #dc3545;">Bs {{ number_format($quotation->tax_amount_bs, 2) }}</td>
            </tr>
            <tr class="grand-total">
                <td class="totals-label" style="background: #1a4a7a; color: white;">TOTAL Bs (con IVA):</td>
                <td class="totals-value" style="background: #1a4a7a; color: white;">Bs {{ number_format($quotation->total_bs, 2) }}</td>
            </tr>
            @else
            <tr>
                <td class="totals-label">Subtotal ({{ $quotation->currency }}):</td>
                <td class="totals-value">{{ $quotation->currency_symbol }}{{ number_format($quotation->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td class="totals-label">Equivalente Bs (sin IVA):</td>
                <td class="totals-value">Bs {{ number_format($quotation->subtotal_bs, 2) }}</td>
            </tr>
            <tr>
                <td class="totals-label" style="color: #dc3545;">IVA 16% Bs:</td>
                <td class="totals-value" style="color: #dc3545;">Bs {{ number_format($quotation->tax_amount_bs, 2) }}</td>
            </tr>
            <tr class="grand-total">
                <td class="totals-label" style="background: #17a2b8; color: white;">TOTAL Bs (con IVA):</td>
                <td class="totals-value" style="background: #17a2b8; color: white;">Bs {{ number_format($quotation->total_bs, 2) }}</td>
            </tr>
            <tr>
                <td class="totals-label" style="background: #1a4a7a; color: white;">Total ({{ $quotation->currency }}):</td>
                <td class="totals-value" style="background: #1a4a7a; color: white;">{{ $quotation->currency_symbol }}{{ number_format($quotation->total, 2) }}</td>
            </tr>
            @endif
        </table>
    </div>

    @if($quotation->notes)
        <div class="notes-section">
            <div class="notes-title">Observaciones</div>
            <div class="notes-content">{{ $quotation->notes }}</div>
        </div>
    @endif

    <div class="footer">
        <p>Documento generado el {{ now()->format('d/m/Y') }} | Sistema de Gestión de Inventario - IAC</p>
    </div>
</body>
</html>
