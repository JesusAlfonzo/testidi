<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acta de Despacho #{{ $dispatch->dispatch_number }}</title>
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
            margin-bottom: 15px;
            border-bottom: 2px solid #5a2ca6;
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
        .info-header {
            font-size: 9px;
            margin-top: 10px;
        }
        .info-header-title {
            font-size: 9px;
            text-transform: uppercase;
            color: #5a2ca6;
            font-weight: bold;
            margin-bottom: 4px;
            border-bottom: 1px solid #5a2ca6;
            padding-bottom: 3px;
        }
        .info-header-item {
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
            color: #5a2ca6;
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
            color: #5a2ca6;
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
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #5a2ca6;
            margin-bottom: 8px;
            text-transform: uppercase;
            border-bottom: 1px solid #ddd;
            padding-bottom: 4px;
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
            background: #5a2ca6;
            color: white;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        
        .signature-section {
            margin-top: 50px;
            width: 100%;
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
        }
        .footer {
            margin-top: 30px;
            padding-top: 12px;
            border-top: 1px solid #ddd;
            font-size: 9px;
            color: #888;
            text-align: center;
        }
        .notes-box {
            border: 1px solid #ddd;
            padding: 8px;
            background: #fafafa;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="header-container">
        <table class="header-table">
            <tr>
                <td class="header-cell header-cell-left">
                    <div class="logo-section">
                        <img src="{{ public_path('images/logo-idi.png') }}" alt="Logo IDI">
                    </div>
                    <div class="info-header">
                        <div class="info-header-title">Información de la Solicitud</div>
                        <div class="info-header-item"><span style="font-weight: bold;">Solicitud N°:</span> REQ-{{ str_pad($dispatch->inventory_request_id, 4, '0', STR_PAD_LEFT) }}</div>
                        <div class="info-header-item"><span style="font-weight: bold;">Solicitante:</span> {{ $dispatch->request->requester->name ?? 'N/A' }}</div>
                        <div class="info-header-item"><span style="font-weight: bold;">Área de Destino:</span> {{ $dispatch->request->destination_area ?? 'No especificada' }}</div>
                        <div class="info-header-item"><span style="font-weight: bold;">Despachado por:</span> {{ $dispatch->dispatcher->name ?? 'N/A' }}</div>
                    </div>
                </td>
                <td class="header-cell header-cell-right">
                    <div class="company-info">
                        <div class="company-name">Instituto de Inmunología - IDI</div>
                        <div class="company-rif">RIF: J-30710739-1</div>
                        <div class="document-info">
                            <div class="document-title">ACTA DE DESPACHO</div>
                            <div class="document-number">N° {{ $dispatch->dispatch_number }}</div>
                            <div class="document-date">Fecha: {{ $dispatch->created_at->format('d/m/Y h:i A') }}</div>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    @if($dispatch->notes)
        <div class="notes-box">
            <span style="font-weight: bold; color: #5a2ca6;">Observaciones del Despacho:</span>
            <p style="margin-top: 3px; line-height: 1.3;">{{ $dispatch->notes }}</p>
        </div>
    @endif

    {{-- SECCIÓN 1: ÍTEMS ENTREGADOS --}}
    @php
        $deliveredItems = $dispatch->items->where('status', 'approved');
        $rejectedItems = $dispatch->items->where('status', 'rejected');
    @endphp

    <div class="section-title">1. Ítems Entregados</div>
    @if($deliveredItems->count() > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 5%; text-align: center;">#</th>
                    <th style="width: 15%;">Código</th>
                    <th style="width: 35%;">Descripción</th>
                    <th style="width: 15%; text-align: center;">Lote/Batch</th>
                    <th style="width: 10%; text-align: center;">Solicitado</th>
                    <th style="width: 10%; text-align: center;">Entregado</th>
                    <th style="width: 10%; text-align: center;">Unidad</th>
                </tr>
            </thead>
            <tbody>
                @foreach($deliveredItems as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $item->product->code ?? '-' }}</td>
                        <td>{{ $item->product->name ?? 'N/A' }}</td>
                        <td class="text-center">{{ $item->batch->batch_number ?? 'N/A' }}</td>
                        <td class="text-center">{{ $item->quantity_requested }}</td>
                        <td class="text-center font-weight-bold" style="color: #28a745;">{{ $item->quantity_dispatched }}</td>
                        <td class="text-center">{{ $item->product->unit->abbreviation ?? 'unid' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="margin-bottom: 20px; font-style: italic; color: #666;">No se entregaron materiales en este despacho.</p>
    @endif

    {{-- SECCIÓN 2: ÍTEMS NEGADOS --}}
    <div class="section-title">2. Ítems Negados</div>
    @if($rejectedItems->count() > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 5%; text-align: center;">#</th>
                    <th style="width: 20%;">Código</th>
                    <th style="width: 50%;">Descripción</th>
                    <th style="width: 15%; text-align: center;">Solicitado</th>
                    <th style="width: 10%; text-align: center;">Entregado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rejectedItems as $index => $item)
                    <tr style="background-color: #fdf2f2;">
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $item->product->code ?? '-' }}</td>
                        <td>{{ $item->product->name ?? 'N/A' }}</td>
                        <td class="text-center">{{ $item->quantity_requested }}</td>
                        <td class="text-center font-weight-bold" style="color: #dc3545;">0</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="margin-bottom: 20px; font-style: italic; color: #666;">No hubo ítems negados en este despacho.</p>
    @endif

    {{-- SECCIÓN 3: SECCIÓN DE FIRMAS --}}
    <div class="signature-section">
        <div class="signature-row">
            <div class="signature-cell">
                <div class="signature-line">
                    <strong>Recibido Por:</strong><br>
                    Nombre: ___________________________<br>
                    Firma y C.I. del Solicitante / Autorizado
                </div>
            </div>
            <div class="signature-cell">
                <div class="signature-line">
                    <strong>Despachado Por:</strong><br>
                    Nombre: {{ $dispatch->dispatcher->name ?? 'N/A' }}<br>
                    Firma y Sello del Operador de Almacén
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>Documento generado el {{ now()->format('d/m/Y') }} | Sistema de Gestión de Inventario - Instituto de Inmunología IDI</p>
    </div>
</body>
</html>
