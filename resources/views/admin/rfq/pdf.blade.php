<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RFQ {{ $rfq->code }}</title>
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
        .title-box {
            background: #f5f5f5;
            padding: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #333;
        }
        .title-box h3 {
            margin: 0 0 5px 0;
            font-size: 14px;
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
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 3px;
            font-size: 11px;
        }
        .status-draft { background: #f0f0f0; }
        .status-sent { background: #d4edda; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Solicitud de Cotización</h1>
        <h2>{{ $rfq->code }}</h2>
    </div>

    <div class="info-box">
        <div class="info-row">
            <span class="info-label">Fecha de Emisión:</span>
            <span class="info-value">{{ $rfq->created_at->format('d/m/Y') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Fecha Límite Respuesta:</span>
            <span class="info-value">{{ $rfq->date_required?->format('d/m/Y') ?? 'No especificada' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Fecha Entrega Requerida:</span>
            <span class="info-value">{{ $rfq->delivery_deadline?->format('d/m/Y') ?? 'No especificada' }}</span>
        </div>
    </div>

    <div class="title-box">
        <h3>{{ $rfq->title }}</h3>
        @if($rfq->description)
            <p style="margin: 5px 0 0 0; white-space: pre-wrap;">{{ $rfq->description }}</p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%">#</th>
                <th style="width: 15%">Código</th>
                <th style="width: 40%">Producto</th>
                <th style="width: 20%">Cantidad</th>
                <th style="width: 20%">Notas</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rfq->items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->product->code ?? 'N/A' }}</td>
                    <td><strong>{{ $item->product->name }}</strong></td>
                    <td class="text-center">{{ $item->quantity }} {{ $item->product->unit->abbreviation ?? 'und' }}</td>
                    <td>{{ $item->notes ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if($rfq->notes)
        <div style="background: #fff3cd; padding: 10px; border-left: 4px solid #ffc107; margin-bottom: 20px;">
            <strong>Notas adicionales:</strong><br>
            {{ $rfq->notes }}
        </div>
    @endif

    <div style="margin-top: 30px;">
        <p><strong>Para responder esta cotización, favor enviar propuesta a:</strong></p>
        <p>Correo: [su-correo@empresa.com]<br>
        Teléfono: [su-teléfono]</p>
    </div>

    <div class="footer">
        <p>Este documento fue generado el {{ now()->format('d/m/Y H:i') }}.</p>
        <p>Sistema de Gestión de Inventario - RFQ {{ $rfq->code }}</p>
    </div>
</body>
</html>
