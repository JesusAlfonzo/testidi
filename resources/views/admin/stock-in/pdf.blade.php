<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acta de Recepción #{{ $stockIn->id }}</title>
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
            border-bottom: 2px solid #28a745;
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
            color: #28a745;
            font-weight: bold;
            margin-bottom: 4px;
            border-bottom: 1px solid #28a745;
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
            color: #28a745;
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
            font-size: 16px;
            font-weight: bold;
            color: #28a745;
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
            color: #28a745;
            font-weight: bold;
            margin-bottom: 5px;
            border-bottom: 1px solid #28a745;
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
        table.items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            page-break-inside: auto;
        }
        table.items-table tr {
            page-break-inside: avoid;
        }
        table.items-table th, table.items-table td {
            border: 1px solid #333;
            padding: 6px;
            text-align: left;
            line-height: 1.2;
        }
        table.items-table th {
            background: #28a745;
            color: white;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        
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
            margin-top: 50px;
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
            margin-top: 45px;
            padding-top: 5px;
            font-size: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header-container">
        <table class="header-table">
            <tr>
                <td class="header-cell header-cell-left">
                    <div class="logo-section">
                        <img src="{{ public_path('images/logo-iac.png') }}" alt="Logo IAC">
                    </div>
                    <div class="supplier-header">
                        <div class="supplier-header-title">Datos del Ingreso</div>
                        <div class="supplier-header-item"><span style="font-weight: bold;">Proveedor:</span> {{ $stockIn->supplier->name ?? 'Ajuste de Stock / N/A' }}</div>
                        @if($stockIn->supplier && $stockIn->supplier->tax_id)
                            <div class="supplier-header-item"><span style="font-weight: bold;">RIF Proveedor:</span> {{ $stockIn->supplier->tax_id }}</div>
                        @endif
                        @if($stockIn->purchaseOrder)
                            <div class="supplier-header-item"><span style="font-weight: bold;">Orden de Compra:</span> {{ $stockIn->purchaseOrder->code }}</div>
                        @endif
                        @if($stockIn->invoice_number)
                            <div class="supplier-header-item"><span style="font-weight: bold;">N° Factura:</span> {{ $stockIn->invoice_number }}</div>
                        @endif
                        @if($stockIn->delivery_note_number)
                            <div class="supplier-header-item"><span style="font-weight: bold;">Nota de Entrega:</span> {{ $stockIn->delivery_note_number }}</div>
                        @endif
                    </div>
                </td>
                <td class="header-cell header-cell-right">
                    <div class="company-info">
                        <div class="company-name">Inmunologia Asociacion Civil</div>
                        <div class="company-rif">RIF: J-30710739-1</div>
                        <div class="document-info">
                            <div class="document-title">ACTA DE RECEPCIÓN DE ALMACÉN</div>
                            <div class="document-number">Acta N° ENT-{{ str_pad($stockIn->id, 4, '0', STR_PAD_LEFT) }}</div>
                            <div class="document-date">Fecha Entrada: {{ $stockIn->entry_date->format('d/m/Y') }}</div>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    @if($stockIn->reason)
    <div class="info-section" style="margin-bottom: 15px;">
        <div class="info-box">
            <div class="info-box-title">Motivo / Observaciones</div>
            <div class="info-item">{{ $stockIn->reason }}</div>
        </div>
    </div>
    @endif

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">#</th>
                <th style="width: 15%;">Código/SKU</th>
                <th style="width: 35%;">Descripción / Producto</th>
                <th style="width: 15%;">Lote</th>
                <th style="width: 10%; text-align: center;">Expiración</th>
                <th style="width: 10%; text-align: center;">Cant.</th>
                <th style="width: 15%;">Ubicación</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stockIn->items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->product->code ?? '-' }}</td>
                    <td>
                        {{ $item->product->name ?? 'N/A' }}
                        @if($item->serial_number)
                            <br><small style="color: #666;">S/N: {{ $item->serial_number }}</small>
                        @endif
                    </td>
                    <td>{{ $item->batch_number ?? '-' }}</td>
                    <td class="text-center">{{ $item->expiration_date ? $item->expiration_date->format('d/m/Y') : 'N/A' }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td>{{ $item->warehouse_location ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="signature-section">
        <table style="width: 100%; border: none;">
            <tr style="border: none;">
                <td style="width: 50%; text-align: center; border: none; padding: 0 30px;">
                    <div class="signature-line">
                        Operador Receptor<br>
                        <span style="font-weight: normal; font-size: 9px; color: #555;">{{ $stockIn->user->name ?? 'N/A' }}</span>
                    </div>
                </td>
                <td style="width: 50%; text-align: center; border: none; padding: 0 30px;">
                    <div class="signature-line">
                        Supervisor de Almacén<br>
                        <span style="font-weight: normal; font-size: 9px; color: #555;">Firma Autorizada</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>Documento generado el {{ now()->format('d/m/Y H:i') }} | Sistema de Gestión de Inventario - IAC</p>
    </div>
</body>
</html>
