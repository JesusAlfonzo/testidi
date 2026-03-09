@extends('adminlte::page')

@section('title', 'Detalle Solicitud #' . $request->id)

@section('content_header')
    <h1>
        Detalle de Solicitud #REQ-{{ $request->id }}
        {{-- 🔑 USO DEL ACCESOR: Muestra "Pendiente", "Aprobada" o "Rechazada" en español --}}
        <span class="badge badge-{{ $request->status_badge_class }}">
            {{ $request->status_label }}
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
                    
                    {{-- 🔑 PROTECCIÓN DE FECHAS NULAS --}}
                    <p><strong>Fecha Solicitud:</strong> {{ optional($request->requested_at)->format('d/m/Y h:i A') }}</p>
                    
                    <p><strong>Ubicación/Área Destino:</strong> {{ $request->destination_area ?? 'N/A' }}</p>
                    
                    @if($request->reference)
                        <p><strong>Referencia:</strong> {{ $request->reference }}</p>
                    @endif

                    <hr>
                    <h4>Justificación</h4>
                    <p>{{ $request->justification }}</p>

                    @if ($request->status !== 'Pending')
                        <hr>
                        <h4>Decisión Final</h4>
                        <p><strong>Procesado por:</strong> {{ $request->approver->name ?? 'Sistema' }}</p>
                        <p><strong>Fecha Procesado:</strong> {{ optional($request->processed_at)->format('d/m/Y h:i A') }}</p>
                        
                        {{-- 🔑 USO DEL ACCESOR DE ESTADO --}}
                        <p><strong>Resolución:</strong> 
                            <span class="text-{{ $request->status_badge_class }} font-weight-bold">
                                {{ strtoupper($request->status_label) }}
                            </span>
                        </p>

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
                            
                            {{-- BOTÓN APROBAR CON MODAL --}}
                            <button type="button" class="btn btn-success btn-lg btn-block mb-3" onclick="confirmAction({
                                title: 'Aprobar Solicitud',
                                message: '¿Está seguro de APROBAR esta solicitud de inventario?',
                                alert: 'Se reducirá el stock de los productos solicitados. Esta acción no se puede deshacer.',
                                confirmBtnClass: 'btn-success',
                                onConfirm: function() {
                                    var form = document.createElement('form');
                                    form.method = 'POST';
                                    form.action = '{{ route('admin.requests.process', ['request' => $request->id]) }}';
                                    var csrfToken = document.querySelector('meta[name=&quot;csrf-token&quot;]').getAttribute('content');
                                    form.innerHTML = '<input type=&quot;hidden&quot; name=&quot;_token&quot; value=&quot;' + csrfToken + '&quot;><input type=&quot;hidden&quot; name=&quot;action&quot; value=&quot;approve&quot;>';
                                    document.body.appendChild(form);
                                    form.submit();
                                }
                            })">
                                <i class="fas fa-check-circle"></i> Aprobar Solicitud
                            </button>
                            
                            {{-- BOTÓN PARA ACTIVAR EL RECHAZO (MODAL) --}}
                            <button type="button" class="btn btn-danger btn-lg btn-block" data-toggle="modal" data-target="#rejectModal">
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
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Producto / Kit</th>
                                <th style="width: 15%">Solicitado</th>
                                <th style="width: 20%">Stock Actual</th>
                                <th style="width: 15%">Costo Unit.</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($request->items as $item)
                                {{-- Lógica para determinar estado de stock y nombres --}}
                                @php
                                    $isKit = $item->item_type === 'kit';
                                    $itemName = $isKit ? ($item->kit->name ?? 'Kit Eliminado') : ($item->product->name ?? 'Producto Eliminado');
                                    $itemCode = $isKit ? 'KIT' : ($item->product->code ?? 'N/A');
                                    $unitAbbr = $isKit ? 'unid' : ($item->product->unit->abbreviation ?? 'unid');
                                    
                                    // Verificación rápida de stock para productos simples
                                    $stockOk = true;
                                    if (!$isKit && $item->product) {
                                        $stockOk = $item->product->stock >= $item->quantity_requested;
                                    }
                                @endphp

                                <tr class="{{ (!$isKit && !$stockOk) ? 'table-danger' : '' }}">
                                    <td>
                                        @if($isKit)
                                            <i class="fas fa-cubes text-info"></i> 
                                        @else
                                            <i class="fas fa-cube text-primary"></i>
                                        @endif
                                        <strong>{{ $itemName }}</strong>
                                        <small class="d-block text-muted">{{ $itemCode }}</small>
                                        
                                        @if($isKit && $item->kit)
                                            <small class="text-muted">Contiene: {{ $item->kit->components->count() }} productos</small>
                                        @endif
                                    </td>
                                    <td class="text-center font-weight-bold">
                                        {{ $item->quantity_requested }} {{ $unitAbbr }}
                                    </td>
                                    <td>
                                        @if(!$isKit)
                                            @if($stockOk)
                                                <span class="badge badge-success">{{ $item->product->stock ?? 0 }} (OK)</span>
                                            @else
                                                <span class="badge badge-danger">{{ $item->product->stock ?? 0 }} (Falta)</span>
                                            @endif
                                        @else
                                            <span class="text-muted text-xs">Ver componentes</span>
                                        @endif
                                    </td>
                                    <td class="text-right">${{ number_format($item->unit_price_at_request, 2) }}</td>
                                </tr>

                                {{-- DESGLOSE DE COMPONENTES SI ES UN KIT --}}
                                @if($isKit && $item->kit && $item->kit->components->count())
                                    <tr>
                                        <td colspan="4" class="bg-light p-0">
                                            <div class="px-4 py-2">
                                                <strong class="text-xs text-muted text-uppercase">Componentes necesarios:</strong>
                                                <ul class="list-unstyled text-sm mt-1 mb-0">
                                                    @foreach($item->kit->components as $comp)
                                                        @php 
                                                            $reqQty = $comp->pivot->quantity_required * $item->quantity_requested;
                                                            $compStockOk = $comp->stock >= $reqQty;
                                                        @endphp
                                                        <li class="d-flex justify-content-between border-bottom pb-1 mb-1 {{ $compStockOk ? '' : 'text-danger font-weight-bold' }}">
                                                            <span>
                                                                <i class="fas fa-angle-right text-muted mr-1"></i> {{ $comp->name }}
                                                            </span>
                                                            <span>
                                                                Req: {{ $reqQty }} | Stock: {{ $comp->stock }}
                                                                @if(!$compStockOk) <i class="fas fa-exclamation-circle"></i> @endif
                                                            </span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @endif

                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3">No hay ítems registrados para esta solicitud.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.requests.index') }}" class="btn btn-default">Volver al Listado</a>
                </div>
            </div>
        </div>
    </div>
    
    {{-- --------------------------------- MODAL DE RECHAZO --------------------------------- --}}
    @if ($request->status === 'Pending' && Gate::allows('solicitudes_aprobar'))
        <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form action="{{ route('admin.requests.process', ['request' => $request->id]) }}" method="POST">
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
                        <div class="modal-footer justify-content-between">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-danger">Confirmar Rechazo</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @include('admin.partials.delete-confirm')
    @include('admin.partials.confirm-action')
@stop