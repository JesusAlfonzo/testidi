<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Solicitudes</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .header { text-align: center; margin-bottom: 20px; }
        .status-pending { color: #e0a800; font-weight: bold; }
        .status-approved { color: green; font-weight: bold; }
        .status-rejected { color: red; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Instituto de Inmunología Dr. Nicolás Bianco</h2>
        <h3>Histórico de Solicitudes de Salida</h3>
        <p>Fecha de emisión: {{ date('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Estado</th>
                <th>Solicitante</th>
                <th>Fecha Solicitud</th>
                <th>Procesado Por</th>
                <th>Justificación</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($requests as $req)
                <tr>
                    <td>REQ-{{ $req->id }}</td>
                    <td>
                        <span class="status-{{ strtolower($req->status) }}">
                            {{ $req->status }}
                        </span>
                    </td>
                    <td>{{ $req->requester->name ?? 'N/A' }}</td>
                    <td>{{ $req->requested_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $req->approver->name ?? '-' }}</td>
                    <td>{{ Str::limit($req->justification, 60) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>