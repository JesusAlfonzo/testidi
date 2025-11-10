@extends('adminlte::page')

@section('title', 'Detalle de Solicitud #' . $request->id)

@section('content_header')
    <h1>
        Detalle de Solicitud de Salida #REQ-{{ $request->id }}
        <span class="badge badge-{{ $request->status === 'Approved' ? 'success' : ($request->status === 'Rejected' ? 'danger' : 'warning') }}">
            {{ $request->status }}
        </span>
    </h1>
@stop

@section('content')
    @include('admin.partials.session-messages')

    <div class="row">
        {{-- --------------------------------- COLUMNA DE DETALLES --------------------------------- --}}
        <div class="col-md-5">
            <div class="card card-info card-outline">
                <div class="card-header">
                    <h3 class="card-title">Información General</h3>
                </div>
                <div class="card-body">
                    <p><strong>Solicitante:</strong> {{ $request->requester->name ?? 'N/A' }}</p>
                    {{-- 🔑 CORRECCIÓN APLICADA: Usamos optional() para evitar el error si la fecha es NULL --}}
                    <p><strong>Fecha Solicitud:</strong> {{ optional($request->requested_at)->format('Y-m-d H:i') }}</p>
                    <p><strong>Ubicación/Área Destino:</strong> {{ $request->destination_area ?? 'N/A' }}</p>

                    <hr>
                    <h4>Justificación</h4>
                    <p>{{ $request->justification }}</p>

                    @if ($request->status !== 'Pending')
                        <hr>
                        <h4>Decisión Final</h4>
                        <p><strong>Procesado por:</strong> {{ $request->approver->name ?? 'Sistema' }}</p>
                        {{-- 🔑 Usamos optional() aquí también por seguridad --}}
                        <p><strong>Fecha Procesado:</strong> {{ optional($request->processed_at)->format('Y-m-d H:i') }}</p>
                        @if ($request->rejection_reason)
                            <div class="alert alert-danger">
                                <strong>Motivo de Rechazo:</strong> {{ $request->rejection_reason }}
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            {{-- --------------------------------- BOTONES DE ACCIÓN (SOLO PENDIENTE) --------------------------------- --}}
            @if ($request->status === 'Pending')
                @can('solicitudes_aprobar')
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Acción de Aprobación</h3>
                        </div>
                        <div class="card-body">
                            <p class="text-danger">¡Atención! La aprobación de esta solicitud ajustará el stock de los productos.</p>

                            {{-- FORMULARIO DE APROBACIÓN --}}
                            <form action="{{ route('admin.requests.process', $request) }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="action" value="approve">
                                <button type="submit" class="btn btn-success btn-lg" onclick="return confirm('¿Confirma que desea APROBAR esta solicitud y reducir el stock de inventario?')">
                                    <i class="fas fa-check-circle"></i> Aprobar Solicitud
                                </button>
                            </form>

                            {{-- BOTÓN PARA ACTIVAR EL RECHAZO --}}
                            <button type="button" class="btn btn-danger btn-lg float-right" data-toggle="modal" data-target="#rejectModal">
                                <i class="fas fa-times-circle"></i> Rechazar
                            </button>
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning">
                        Esta solicitud está pendiente de aprobación. Su rol no tiene permisos para tomar decisiones.
                    </div>
                @endcan
            @endif
        </div>

        {{-- --------------------------------- COLUMNA DE ÍTEMS --------------------------------- --}}
        <div class="col-md-7">
            <div class="card card-secondary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Ítems Solicitados</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th style="width: 15%">Solicitado</th>
                                <th style="width: 15%">Stock Actual</th>
                                <th style="width: 15%">Costo Unitario</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($request->items as $item)
                                <tr>
                                    <td>
                                        <strong>{{ $item->product->name ?? 'Producto Eliminado' }}</strong>
                                        <small class="d-block text-muted">{{ $item->product->code ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        {{ $item->quantity_requested }} {{ $item->product->unit->abbreviation ?? 'unid' }}
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ ($item->product->stock ?? 0) < $item->quantity_requested ? 'danger' : 'success' }}">
                                            {{ $item->product->stock ?? 0 }}
                                        </span>
                                    </td>
                                    <td>${{ number_format($item->unit_price_at_request, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No hay ítems registrados para esta solicitud.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- --------------------------------- MODAL DE RECHAZO --------------------------------- --}}
    @if ($request->status === 'Pending' && Gate::allows('solicitudes_aprobar'))
        <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form action="{{ route('admin.requests.process', $request) }}" method="POST">
                        @csrf
                        <input type="hidden" name="action" value="reject">
                        <div class="modal-header bg-danger">
                            <h5 class="modal-title" id="rejectModalLabel">Rechazar Solicitud #REQ-{{ $request->id }}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="rejection_reason">Motivo del Rechazo</label>
                                <textarea name="rejection_reason" id="rejection_reason" class="form-control" rows="3" required placeholder="Explique brevemente por qué se rechaza la solicitud."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-danger">Confirmar Rechazo</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@stop
