<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cotización {{ $quotation->code }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
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
            text-transform: uppercase;
        }
        .header h2 {
            margin: 5px 0 0 0;
            font-size: 14px;
            color: #666;
        }
        .info-box {
            margin-bottom: 20px;
        }
        .info-row {
            display: flex;
            margin-bottom: 5px;
        }
        .info-label {
            font-weight: bold;
            width: 150px;
        }
        .info-value {
            flex: 1;
        }
        .two-columns {
            display: flex;
            gap: 40px;
        }
        .column {
            flex: 1;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background: #f5f5f5;
            font-weight: bold;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Cotización de Proveedor</h1>
        <h2>{{ $quotation->code }}</h2>
    </div>

    <div class="two-columns">
        <div class="column">
            <h4>Proveedor</h4>
            <div class="info-box">
                <p><strong>{{ $quotation->getSupplierDisplayName() }}</strong></p>
                @if($quotation->getSupplierDisplayEmail() != '-')
                    <p>{{ $quotation->getSupplierDisplayEmail() }}</p>
                @endif
                @if($quotation->supplier_phone_temp || ($quotation->supplier && $quotation->supplier->phone))
                    <p>{{ $quotation->supplier_phone_temp ?? $quotation->supplier->phone }}</p>
                @endif
            </div>
        </div>
        <div class="column">
            <h4>Información</h4>
            <div class="info-box">
                <div class="info-row">
                    <span class="info-label">Fecha Emisión:</span>
                    <span class="info-value">{{ $quotation->date_issued->format('d/m/Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Válido hasta:</span>
                    <span class="info-value">{{ $quotation->valid_until?->format('d/m/Y') ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Entrega:</span>
                    <span class="info-value">{{ $quotation->delivery_date?->format('d/m/Y') ?? '-' }}</span>
                </div>
                @if($quotation->supplier_reference)
                    <div class="info-row">
                        <span class="info-label">Ref. Proveedor:</span>
                        <span class="info-value">{{ $quotation->supplier_reference }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%">#</th>
                <th style="width: 45%">Producto</th>
                <th style="width: 15%">Cantidad</th>
                <th style="width: 15%">Costo Unit.</th>
                <th style="width: 20%">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quotation->items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->product_name }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">${{ number_format($item->unit_cost, 2) }}</td>
                    <td class="text-right">${{ number_format($item->total_cost, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-right"><strong>Subtotal:</strong></td>
                <td class="text-right"><strong>${{ number_format($quotation->subtotal, 2) }}</strong></td>
            </tr>
            <tr>
                <td colspan="4" class="text-right"><strong>Total ({{ $quotation->currency }}):</strong></td>
                <td class="text-right"><strong style="font-size: 14px;">${{ number_format($quotation->total, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    @if($quotation->notes)
        <div style="background: #f5f5f5; padding: 10px; border-left: 4px solid #666; margin-bottom: 20px;">
            <strong>Notas:</strong><br>
            {{ $quotation->notes }}
        </div>
    @endif

    <div class="footer">
        <p>Documento generado el {{ now()->format('d/m/Y H:i') }}</p>
        <p>Sistema de Gestión de Inventario - Cotización {{ $quotation->code }}</p>
    </div>
</body>
</html>
