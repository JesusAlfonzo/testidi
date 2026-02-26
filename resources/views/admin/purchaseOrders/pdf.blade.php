<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden de Compra {{ $purchaseOrder->code }}</title>
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
            font-size: 20px;
            text-transform: uppercase;
        }
        .header h2 {
            margin: 5px 0 0 0;
            font-size: 14px;
            color: #666;
        }
        .two-columns {
            display: flex;
            gap: 40px;
            margin-bottom: 20px;
        }
        .column {
            flex: 1;
        }
        .info-box {
            border: 1px solid #ddd;
            padding: 10px;
            background: #f9f9f9;
        }
        .info-box h4 {
            margin: 0 0 10px 0;
            font-size: 12px;
            text-transform: uppercase;
            color: #666;
        }
        .info-row {
            margin-bottom: 5px;
        }
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 100px;
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
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #666;
        }
        .terms {
            background: #f9f9f9;
            padding: 15px;
            border: 1px solid #ddd;
            margin-bottom: 20px;
        }
        .terms h4 {
            margin: 0 0 10px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Orden de Compra</h1>
        <h2>{{ $purchaseOrder->code }}</h2>
    </div>

    <div class="two-columns">
        <div class="column">
            <div class="info-box">
                <h4>Proveedor</h4>
                <p><strong>{{ $purchaseOrder->supplier->name }}</strong></p>
                @if($purchaseOrder->supplier->tax_id)
                    <p>RIF/NIT: {{ $purchaseOrder->supplier->tax_id }}</p>
                @endif
                @if($purchaseOrder->supplier->contact_person)
                    <p>Contacto: {{ $purchaseOrder->supplier->contact_person }}</p>
                @endif
                @if($purchaseOrder->supplier->phone)
                    <p>Tel: {{ $purchaseOrder->supplier->phone }}</p>
                @endif
                @if($purchaseOrder->supplier->email)
                    <p>Email: {{ $purchaseOrder->supplier->email }}</p>
                @endif
            </div>
        </div>
        <div class="column">
            <div class="info-box">
                <h4>Información</h4>
                <div class="info-row">
                    <span class="info-label">Fecha:</span>
                    <span>{{ $purchaseOrder->date_issued->format('d/m/Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Entrega:</span>
                    <span>{{ $purchaseOrder->delivery_date?->format('d/m/Y') ?? 'A convenir' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Dirección:</span>
                    <span>{{ $purchaseOrder->delivery_address ?? 'A confirmar' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Moneda:</span>
                    <span>{{ $purchaseOrder->currency }}</span>
                </div>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%">#</th>
                <th style="width: 10%">Código</th>
                <th style="width: 40%">Descripción</th>
                <th style="width: 15%">Cantidad</th>
                <th style="width: 15%">Costo Unit.</th>
                <th style="width: 15%">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchaseOrder->items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->product_code ?? '-' }}</td>
                    <td>{{ $item->product_name }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">${{ number_format($item->unit_cost, 2) }}</td>
                    <td class="text-right">${{ number_format($item->total_cost, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-right"><strong>Subtotal:</strong></td>
                <td class="text-right"><strong>${{ number_format($purchaseOrder->subtotal, 2) }}</strong></td>
            </tr>
            <tr>
                <td colspan="5" class="text-right"><strong>TOTAL ({{ $purchaseOrder->currency }}):</strong></td>
                <td class="text-right"><strong style="font-size: 14px;">${{ number_format($purchaseOrder->total, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    @if($purchaseOrder->terms)
        <div class="terms">
            <h4>Términos y Condiciones</h4>
            <div style="white-space: pre-wrap;">{{ $purchaseOrder->terms }}</div>
        </div>
    @endif

    <div class="footer">
        <p>Este documento fue generado el {{ now()->format('d/m/Y H:i') }}</p>
        <p>Orden de Compra {{ $purchaseOrder->code }} - Sistema de Gestión de Inventario</p>
    </div>
</body>
</html>
