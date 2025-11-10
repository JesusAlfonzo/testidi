@extends('adminlte::page')

@section('title', 'Inventario | Solicitudes de Salida')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1>📑 Solicitudes de Salida</h1>
        @can('solicitudes_crear')
            <a href="{{ route('admin.requests.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Crear Solicitud
            </a>
        @endcan
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            @include('admin.partials.session-messages')

            <div class="card">
                <div class="card-body p-0">
                    <table class="table table-striped table-hover table-sm">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Solicitante</th>
                                <th>Justificación</th>
                                <th>Fecha Solicitud</th>
                                <th>Aprobador</th>
                                <th>Estado</th>
                                <th width="120px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $badgeColors = ['Pending' => 'warning', 'Approved' => 'success', 'Rejected' => 'danger'];
                            @endphp

                            @forelse ($requests as $request)
                                <tr>
                                    <td>REQ-{{ $request->id }}</td>
                                    <td>{{ $request->requester->name ?? 'N/A' }}</td>
                                    <td>{{ Str::limit($request->justification, 50) }}</td>
                                    <td>{{ optional($request->requested_at)->format('Y-m-d H:i') }}</td>
                                    <td>{{ $request->approver->name ?? '---' }}</td>
                                    <td>
                                        <span class="badge badge-{{ $badgeColors[$request->status] ?? 'secondary' }}">
                                            {{ $request->status }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.requests.show', $request) }}" class="btn btn-xs btn-default text-info" title="Ver Detalle">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        {{-- 📢 PUNTO CLAVE DEL APROBADOR --}}
                                        @if ($request->status === 'Pending')
                                            @can('solicitudes_aprobar')
                                                {{-- Al hacer clic en este botón, el Aprobador será llevado a la vista SHOW --}}
                                                {{-- donde se detallan los ítems y están los botones de Aprobar/Rechazar. --}}
                                                <a href="{{ route('admin.requests.show', $request) }}" class="btn btn-xs btn-default text-warning" title="Revisar y Aprobar/Rechazar">
                                                    <i class="fas fa-check-circle"></i>
                                                </a>
                                            @endcan
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">No se encontraron solicitudes.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    {{ $requests->links() }}
                </div>
            </div>
        </div>
    </div>
@stop
