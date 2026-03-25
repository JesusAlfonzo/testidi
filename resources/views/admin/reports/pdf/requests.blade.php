<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Solicitudes</title>
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
        .document-date {
            font-size: 11px;
            color: #666;
        }
        .summary-section {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .summary-box {
            flex: 1;
            border: 1px solid #ddd;
            padding: 12px;
            background: #fafafa;
            text-align: center;
        }
        .summary-label {
            font-size: 10px;
            text-transform: uppercase;
            color: #666;
            margin-bottom: 5px;
        }
        .summary-value {
            font-size: 24px;
            font-weight: bold;
            color: #1a4a7a;
        }
        .summary-value.warning { color: #dc3545; }
        .summary-value.success { color: #28a745; }
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
        .status-pending {
            background: #fff3cd;
            color: #856404;
            padding: 3px 10px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 10px;
        }
        .status-approved, .status-approved {
            background: #d4edda;
            color: #155724;
            padding: 3px 10px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 10px;
        }
        .status-rejected, .status-rejected {
            background: #f8d7da;
            color: #721c24;
            padding: 3px 10px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 10px;
        }
        .footer {
            margin-top: 20px;
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
                    <div class="document-title">REPORTE DE SOLICITUDES</div>
                    <div class="document-date">Generado: {{ now()->format('d/m/Y') }}</div>
                </div>
            </div>
        </div>
    </div>

    @php
        $total = $requests->count();
        $pendientes = $requests->filter(fn($r) => $r->status === 'Pending')->count();
        $aprobadas = $requests->filter(fn($r) => $r->status === 'Approved')->count();
        $rechazadas = $requests->filter(fn($r) => $r->status === 'Rejected')->count();
    @endphp

    <div class="summary-section">
        <div class="summary-box">
            <div class="summary-label">Total Solicitudes</div>
            <div class="summary-value">{{ $total }}</div>
        </div>
        <div class="summary-box">
            <div class="summary-label">Pendientes</div>
            <div class="summary-value warning">{{ $pendientes }}</div>
        </div>
        <div class="summary-box">
            <div class="summary-label">Aprobadas</div>
            <div class="summary-value success">{{ $aprobadas }}</div>
        </div>
        <div class="summary-box">
            <div class="summary-label">Rechazadas</div>
            <div class="summary-value warning">{{ $rechazadas }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 10%;">ID</th>
                <th style="width: 12%; text-align: center;">Estado</th>
                <th style="width: 18%;">Solicitante</th>
                <th style="width: 15%;">Fecha Solicitud</th>
                <th style="width: 15%;">Procesado Por</th>
                <th style="width: 30%;">Justificación</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($requests as $req)
                @php
                    $statusClass = match($req->status) {
                        'Pending' => 'status-pending',
                        'Approved' => 'status-approved',
                        'Rejected' => 'status-rejected',
                        default => ''
                    };
                    $statusLabel = match($req->status) {
                        'Pending' => 'Pendiente',
                        'Approved' => 'Aprobada',
                        'Rejected' => 'Rechazada',
                        default => $req->status
                    };
                @endphp
                <tr>
                    <td>REQ-{{ $req->id }}</td>
                    <td class="text-center">
                        <span class="{{ $statusClass }}">{{ $statusLabel }}</span>
                    </td>
                    <td>{{ $req->requester->name ?? 'N/A' }}</td>
                    <td>{{ $req->requested_at ? $req->requested_at->format('d/m/Y H:i') : '-' }}</td>
                    <td>{{ $req->approver->name ?? '-' }}</td>
                    <td>{{ Str::limit($req->justification, 60) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Documento generado el {{ now()->format('d/m/Y H:i') }} | Sistema de Gestión de Inventario - IAC</p>
    </div>
</body>
</html>
