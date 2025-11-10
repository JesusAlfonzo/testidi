@extends('adminlte::page')

@section('title', 'Reporte de Solicitudes de Inventario')

@section('content_header')
    <h1>Reporte de Solicitudes (Movimientos de Salida)</h1>
@stop

@section('content')
    @php
        // Definición de colores de estado para las insignias (badges)
        $badgeColors = [
            'Pending' => 'warning',
            'Approved' => 'success',
            'Rejected' => 'danger',
        ];
    @endphp

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Histórico de Solicitudes de Salida</h3>
            <div class="card-tools">
                <a href="#" class="btn btn-tool btn-sm">
                    <i class="fas fa-download"></i> Exportar a CSV (Futuro)
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-valign-middle">
                <thead>
                    <tr>
                        <th style="width: 10%">ID</th>
                        <th style="width: 15%">Estado</th>
                        <th style="width: 20%">Solicitante</th>
                        <th>Justificación</th>
                        <th style="width: 15%">Fecha Solicitud</th>
                        <th style="width: 15%">Procesado por</th>
                        <th style="width: 5%">Detalle</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($requests as $request)
                        @php
                            $badge = $badgeColors[$request->status] ?? 'secondary';
                        @endphp
                        <tr>
                            <td>REQ-{{ $request->id }}</td>
                            <td>
                                <span class="badge badge-{{ $badge }}">{{ $request->status }}</span>
                            </td>
                            <td>{{ $request->requester->name ?? 'N/A' }}</td>
                            <td>{{ Str::limit($request->justification, 50) }}</td>
                            <td>{{ $request->requested_at->format('Y-m-d H:i') }}</td>
                            <td>{{ $request->approver->name ?? 'Pendiente' }}</td>
                            <td>
                                <a href="{{ route('admin.requests.show', $request->id) }}" class="btn btn-xs btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix">
            {{ $requests->links() }}
        </div>
    </div>
@stop
