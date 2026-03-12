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
            margin-bottom: 20px;
            border-bottom: 2px solid #17a2b8;
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
            color: #17a2b8;
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
            color: #17a2b8;
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
            color: #17a2b8;
            font-weight: bold;
            margin-bottom: 8px;
            border-bottom: 1px solid #17a2b8;
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
            background: #17a2b8;
            color: white;
            font-weight: bold;
            font-size: 10px;
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
                    <div class="document-title">SOLICITUD DE SALIDA</div>
                    <div class="document-number">N° REQ-{{ str_pad($request->id, 4, '0', STR_PAD_LEFT) }}</div>
                    <div class="document-date">Fecha: {{ $request->requested_at->format('d/m/Y') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="info-section">
        <div class="info-box">
            <div class="info-box-title">Datos del Solicitante</div>
            <div class="info-item"><span class="info-label">Nombre:</span> {{ $request->requester->name ?? 'N/A' }}</div>
            @if($request->requester && $request->requester->email)
                <div class="info-item"><span class="info-label">Email:</span> {{ $request->requester->email }}</div>
            @endif
        </div>
        <div class="info-box">
            <div class="info-box-title">Información de la Solicitud</div>
            <div class="info-item"><span class="info-label">Estado:</span> <span class="status-box status-{{ $request->status }}">{{ $request->status === 'Pending' ? 'Pendiente' : ($request->status === 'Approved' ? 'Aprobada' : 'Rechazada') }}</span></div>
            <div class="info-item"><span class="info-label">Área de Destino:</span> {{ $request->destination_area ?? 'No especificada' }}</div>
            <div class="info-item"><span class="info-label">Referencia:</span> {{ $request->reference ?? 'No especificada' }}</div>
            @if($request->approver)
                <div class="info-item"><span class="info-label">Aprobado por:</span> {{ $request->approver->name }}</div>
                <div class="info-item"><span class="info-label">Fecha de Aprobación:</span> {{ $request->processed_at ? $request->processed_at->format('d/m/Y H:i') : '-' }}</div>
            @endif
        </div>
    </div>

    <div class="info-box" style="margin-bottom: 20px;">
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

    <div class="footer">
        <p>Documento generado el {{ now()->format('d/m/Y') }} | Sistema de Gestión de Inventario - IAC</p>
    </div>
</body>
</html>
