<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de Salida #{{ $request->id }}</title>
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
            font-size: 9px;
            text-transform: uppercase;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        
        .status-box {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 9px;
        }
        .status-Pending { background-color: #ffc107; color: #000; }
        .status-Approved { background-color: #28a745; color: #fff; }
        .status-Rejected { background-color: #dc3545; color: #fff; }

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
            margin-top: 40px;
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
        .warning-section {
            margin-top: 30px;
            padding: 10px;
            border: 1px solid #dc3545;
            background: #f8d7da;
            color: #721c24;
            font-size: 10px;
            text-align: center;
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
                    <div class="supplier-header">
                        <div class="supplier-header-title">Datos del Solicitante</div>
                        <div class="supplier-header-item"><span style="font-weight: bold;">Nombre:</span> {{ $request->requester->name ?? 'N/A' }}</div>
                        @if($request->requester && $request->requester->email)
                            <div class="supplier-header-item"><span style="font-weight: bold;">Email:</span> {{ $request->requester->email }}</div>
                        @endif
                        <div class="supplier-header-item"><span style="font-weight: bold;">Área de Destino:</span> {{ $request->destination_area ?? 'No especificada' }}</div>
                        @if($request->approver)
                            <div class="supplier-header-item"><span style="font-weight: bold;">{{ $request->status === 'Approved' ? 'Aprobado' : 'Rechazado' }} por:</span> {{ $request->approver->name }}</div>
                            <div class="supplier-header-item"><span style="font-weight: bold;">Fecha de {{ $request->status === 'Approved' ? 'Aprobación' : 'Rechazo' }}:</span> {{ $request->processed_at ? $request->processed_at->format('d/m/Y H:i') : '-' }}</div>
                        @endif
                    </div>
                </td>
                <td class="header-cell header-cell-right">
                    <div class="company-info">
                        <div class="company-name">Inmunologia Asociacion Civil</div>
                        <div class="company-rif">RIF: J-30710739-1</div>
                        <div class="document-info">
                            <div class="document-title">SOLICITUD DE SALIDA</div>
                            <div class="document-number">N° REQ-{{ str_pad($request->id, 4, '0', STR_PAD_LEFT) }}</div>
                            <div class="document-date">Fecha: {{ $request->requested_at->format('d/m/Y') }}</div>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="info-box" style="margin-bottom: 15px;">
        <div class="info-box-title">Justificación</div>
        <div class="info-item">{{ $request->justification }}</div>
        @if($request->rejection_reason)
            <div class="info-item" style="margin-top: 10px; color: #dc3545;">
                <span class="info-label">Motivo de Rechazo:</span> {{ $request->rejection_reason }}
            </div>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">#</th>
                <th style="width: 15%;">Código</th>
                <th style="width: 40%;">Descripción</th>
                <th style="width: 10%; text-align: center;">Tipo</th>
                <th style="width: 10%; text-align: center;">Cantidad</th>
            </tr>
        </thead>
        <tbody>
            @foreach($request->items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->item_type === 'product' ? ($item->product->code ?? '-') : ($item->kit->code ?? '-') }}</td>
                    <td>
                        @if($item->item_type === 'product')
                            {{ $item->product->name ?? 'N/A' }}
                        @else
                            {{ $item->kit->name ?? 'N/A' }}
                        @endif
                    </td>
                    <td class="text-center">{{ $item->item_type === 'product' ? 'Producto' : 'Kit' }}</td>
                    <td class="text-center">{{ $item->quantity_requested }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="signature-section">
        <div class="signature-row">
            <div class="signature-cell">
                <div class="signature-line">Firma y Nombre del Solicitante</div>
            </div>
            <div class="signature-cell">
                <div class="signature-line">Firma y Nombre del Autorizador</div>
            </div>
        </div>
    </div>

    <div class="warning-section">
        <strong>NOTA:</strong> Toda solicitud de requisición debe ser firmada por su jefe inmediato
    </div>

    <div class="footer">
        <p>Documento generado el {{ now()->format('d/m/Y') }} | Sistema de Gestión de Inventario - IAC</p>
    </div>
</body>
</html>
