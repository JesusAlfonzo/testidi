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
        .header-info-box {
            margin-top: 10px;
        }
        .header-info-title {
            font-size: 9px;
            text-transform: uppercase;
            color: #1a4a7a;
            font-weight: bold;
            margin-bottom: 5px;
            border-bottom: 1px solid #1a4a7a;
            padding-bottom: 3px;
        }
        .header-info-item {
            margin-bottom: 3px;
            line-height: 1.3;
            font-size: 9px;
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
        .notes-section {
            margin-bottom: 15px;
            page-break-inside: avoid;
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
            padding: 12px;
            margin-bottom: 15px;
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
            margin-top: 30px;
            padding-top: 12px;
            border-top: 1px solid #ddd;
            font-size: 9px;
            color: #888;
            text-align: center;
            page-break-inside: avoid;
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
                    <div class="header-info-box">
                        <div class="header-info-title">Información de la Solicitud</div>
                        @if($rfq->description)
                            <div class="header-info-item"><span style="font-weight: bold;">Descripción:</span> {{ $rfq->description }}</div>
                        @endif
<?php
    $statusLabels = [
        'draft' => 'Borrador',
        'sent' => 'Enviada',
        'closed' => 'Cerrada',
        'cancelled' => 'Cancelada',
    ];
    $statusLabel = $statusLabels[$rfq->status] ?? ucfirst($rfq->status);
?>
                        <div class="header-info-item"><span style="font-weight: bold;">Fecha de Emisión:</span> {{ $rfq->created_at->format('d/m/Y') }}</div>
                        <div class="header-info-item"><span style="font-weight: bold;">Fecha Límite:</span> {{ $rfq->date_required?->format('d/m/Y') ?? 'No especificada' }}</div>
                        <div class="header-info-item"><span style="font-weight: bold;">Fecha Entrega:</span> {{ $rfq->delivery_deadline?->format('d/m/Y') ?? 'A convenir' }}</div>
                        <div class="header-info-item"><span style="font-weight: bold;">Estado:</span> {{ $statusLabel }}</div>
                        <div class="header-info-item"><span style="font-weight: bold;">Solicitado por:</span> {{ $rfq->creator->name ?? 'Sistema' }}</div>
                        <div class="header-info-item"><span style="font-weight: bold;">Total Items:</span> {{ $rfq->items->count() }}</div>
                    </div>
                </td>
                <td class="header-cell header-cell-right">
                    <div class="company-info">
                        <div class="company-name">Inmunologia Asociacion Civil</div>
                        <div class="company-rif">RIF: J-30710739-1</div>
                        <div class="document-info">
                            <div class="document-title">SOLICITUD DE COTIZACIÓN</div>
                            <div class="document-number">N° {{ $rfq->code }}</div>
                            <div class="document-date">Fecha: {{ $rfq->created_at->format('d/m/Y') }}</div>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">#</th>
                <th style="width: 12%;">Código</th>
                <th style="width: 45%;">Descripción / Producto</th>
                <th style="width: 10%; text-align: center;">Cant.</th>
                <th style="width: 10%; text-align: center;">Und.</th>
                <th style="width: 18%;">Notas</th>
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
