<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RFQ {{ $rfq->code }}</title>
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
        .title-section {
            background: #f0f5fa;
            border: 1px solid #1a4a7a;
            padding: 15px;
            margin-bottom: 20px;
        }
        .title-main {
            font-size: 14px;
            font-weight: bold;
            color: #1a4a7a;
            margin-bottom: 8px;
        }
        .title-description {
            font-size: 11px;
            line-height: 1.5;
            white-space: pre-wrap;
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
        .contact-section {
            background: #1a4a7a;
            color: white;
            padding: 15px;
            margin-bottom: 20px;
            text-align: center;
        }
        .contact-title {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 8px;
        }
        .contact-info {
            font-size: 11px;
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
                    <div class="document-title">SOLICITUD DE COTIZACIÓN</div>
                    <div class="document-number">N° {{ $rfq->code }}</div>
                    <div class="document-date">Fecha: {{ $rfq->created_at->format('d/m/Y') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="title-section">
        <div class="title-main">{{ $rfq->title }}</div>
        @if($rfq->description)
            <div class="title-description">{{ $rfq->description }}</div>
        @endif
    </div>

    <div class="info-section">
        <div class="info-box">
            <div class="info-box-title">Fechas</div>
            <div class="info-item"><span class="info-label">Fecha de Emisión:</span> {{ $rfq->created_at->format('d/m/Y') }}</div>
            <div class="info-item"><span class="info-label">Fecha Límite:</span> {{ $rfq->date_required?->format('d/m/Y') ?? 'No especificada' }}</div>
            <div class="info-item"><span class="info-label">Fecha Entrega:</span> {{ $rfq->delivery_deadline?->format('d/m/Y') ?? 'A convenir' }}</div>
        </div>
        <div class="info-box">
            <div class="info-box-title">Información</div>
<?php
    $statusLabels = [
        'draft' => 'Borrador',
        'sent' => 'Enviada',
        'closed' => 'Cerrada',
        'cancelled' => 'Cancelada',
    ];
    $statusLabel = $statusLabels[$rfq->status] ?? ucfirst($rfq->status);
?>
            <div class="info-item"><span class="info-label">Estado:</span> {{ $statusLabel }}</div>
            <div class="info-item"><span class="info-label">Solicitado por:</span> {{ $rfq->creator->name ?? 'Sistema' }}</div>
            <div class="info-item"><span class="info-label">Total Items:</span> {{ $rfq->items->count() }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">#</th>
                <th style="width: 12%;">Código</th>
                <th style="width: 43%;">Descripción / Producto</th>
                <th style="width: 10%; text-align: center;">Cant.</th>
                <th style="width: 10%; text-align: center;">Und.</th>
                <th style="width: 20%;">Notas</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rfq->items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->product->code ?? 'N/A' }}</td>
                    <td><strong>{{ $item->product->name }}</strong></td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-center">{{ $item->product->unit->abbreviation ?? 'und' }}</td>
                    <td>{{ $item->notes ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if($rfq->notes)
        <div class="notes-section">
            <div class="notes-title">Notas Adicionales</div>
            <div class="notes-content">{{ $rfq->notes }}</div>
        </div>
    @endif

    <div class="contact-section">
        <div class="contact-title">Para responder esta cotización, favor contactarnos</div>
        <div class="contact-info">Inmunologia Asociacion Civil | RIF: J-30710739-1</div>
    </div>

    <div class="footer">
        <p>Documento generado el {{ now()->format('d/m/Y') }} | Sistema de Gestión de Inventario - IAC</p>
    </div>
</body>
</html>
