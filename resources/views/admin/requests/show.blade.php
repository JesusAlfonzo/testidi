@extends('adminlte::page')

@section('title', 'Detalle de Solicitud #' . $request->id)

@section('content_header')
    <h1>
        Detalle de Solicitud de Salida #REQ-{{ $request->id }}
        {{-- Muestra el estado de la solicitud --}}
        <span class="badge badge-{{ $request->status === 'Approved' ? 'success' : ($request->status === 'Rejected' ? 'danger' : 'warning') }} float-right">
            <i class="fas fa-clipboard-list"></i> {{ $request->status }}
        </span>
    </h1>
@stop

@section('content')
    @include('admin.partials.session-messages')

    <div class="row">
        {{-- =================================== COLUMNA DE DETALLES Y ACCIÓN =================================== --}}
        <div class="col-md-5">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">Información General</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                    </div>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Solicitante:</dt>
                        <dd class="col-sm-7">{{ $request->requester->name ?? 'N/A' }}</dd>

                        <dt class="col-sm-5">Área Destino:</dt>
                        <dd class="col-sm-7">{{ $request->destination_area ?? 'N/A' }}</dd>

                        <dt class="col-sm-5">Fecha Solicitud:</dt>
                        <dd class="col-sm-7">{{ optional($request->requested_at)->format('Y-m-d H:i') }}</dd>
                    </dl>

                    <hr class="mt-3 mb-3">
                    <h5 class="mb-2"><i class="fas fa-pencil-alt text-muted"></i> Justificación:</h5>
                    <p class="text-muted">{{ $request->justification }}</p>

                    @if ($request->status !== 'Pending')
                        <hr class="mt-3 mb-3">
                        <h5 class="mb-2">Decisión Final ({{ $request->status }})</h5>
                        <p class="mb-0"><strong>Procesado por:</strong> {{ $request->approver->name ?? 'Sistema' }}</p>
                        <p><strong>Fecha Procesado:</strong> {{ optional($request->processed_at)->format('Y-m-d H:i') }}</p>
                        @if ($request->rejection_reason)
                            <div class="alert alert-danger p-2 mt-2 mb-0">
                                <strong>Motivo Rechazo:</strong> {{ $request->rejection_reason }}
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
                            <h3 class="card-title">Tomar Decisión</h3>
                        </div>
                        <div class="card-body">
                            <p class="text-danger small"><i class="fas fa-exclamation-triangle"></i> La aprobación ajustará el stock. Verifique la disponibilidad de los ítems en la tabla de la derecha.</p>

                            {{-- Formulario de Aprobación --}}
                            <form action="{{ route('admin.requests.process', $request) }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="action" value="approve">
                                <button type="submit" class="btn btn-success btn-lg btn-block mb-2" onclick="return confirm('¿Confirma que desea APROBAR esta solicitud y reducir el stock de inventario?')">
                                    <i class="fas fa-check-circle"></i> Aprobar Solicitud
                                </button>
                            </form>

                            {{-- Botón para activar el Rechazo --}}
                            <button type="button" class="btn btn-danger btn-block" data-toggle="modal" data-target="#rejectModal">
                                <i class="fas fa-times-circle"></i> Rechazar Solicitud
                            </button>
                        </div>
                    </div>
                @endcan
            @endif
        </div>

        {{-- =================================== COLUMNA DE ÍTEMS SOLICITADOS =================================== --}}
        <div class="col-md-7">
            <div class="card card-secondary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-list-alt"></i> Detalle de Ítems</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>Ítem Solicitado</th>
                                <th class="text-center" style="width: 15%">Cantidad</th>
                                <th class="text-center" style="width: 15%">Stock Requerido</th>
                                <th class="text-right" style="width: 15%">Costo Unitario</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($request->items as $item)
                                @php
                                    // 1. Determinar el ítem base (Producto o Kit)
                                    $baseItem = $item->item_type === 'product' ? $item->product : $item->kit;
                                    $isKit = $item->item_type === 'kit';
                                    $itemName = $baseItem->name ?? 'ÍTEM ELIMINADO';
                                    
                                    // 2. Calcular el stock global (solo para mostrar la advertencia en la fila principal)
                                    $hasInsufficientStock = false;
                                    
                                    if ($isKit && $baseItem) {
                                        // Para kits, iteramos los componentes
                                        foreach ($baseItem->components as $component) {
                                            $totalConsumption = $item->quantity_requested * $component->pivot->quantity_required;
                                            if ($component->stock < $totalConsumption) {
                                                $hasInsufficientStock = true;
                                                break;
                                            }
                                        }
                                    } elseif (!$isKit && $baseItem) {
                                        // Para productos simples, validamos directamente
                                        if ($baseItem->stock < $item->quantity_requested) {
                                            $hasInsufficientStock = true;
                                        }
                                    }

                                    $stockBadgeClass = $hasInsufficientStock ? 'danger' : 'success';
                                @endphp

                                {{-- Fila principal: Producto o Kit --}}
                                <tr class="{{ $hasInsufficientStock ? 'table-danger' : '' }}">
                                    <td>
                                        <i class="fas fa-{{ $isKit ? 'cubes text-info' : 'cube text-primary' }}"></i> 
                                        <strong>{{ $itemName }}</strong> 
                                        @if($isKit)
                                            <small class="d-block text-muted">({{ $baseItem->components->count() ?? 0 }} Productos)</small>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <strong>{{ $item->quantity_requested }}</strong> 
                                        <span class="small text-muted">{{ $item->product->unit->abbreviation ?? 'unid' }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($hasInsufficientStock)
                                            <span class="badge badge-danger" title="Stock insuficiente para aprobar">
                                                <i class="fas fa-exclamation-circle"></i> Faltante
                                            </span>
                                        @else
                                            <span class="badge badge-success">OK</span>
                                        @endif
                                    </td>
                                    <td class="text-right">${{ number_format($item->unit_price_at_request, 2) }}</td>
                                </tr>
                                
                                {{-- Fila secundaria: Componentes del Kit (Mejorada) --}}
                                @if($isKit && $baseItem && $baseItem->components->count())
                                    <tr class="p-0">
                                        <td colspan="4" class="p-0 border-0">
                                            <div class="card card-body p-2 m-0 border-left-0 border-right-0 border-top-0 bg-light">
                                                <strong class="text-info small mb-1"><i class="fas fa-sitemap"></i> Componentes necesarios para {{ $item->quantity_requested }} Kits:</strong>
                                                <ul class="list-unstyled mb-0 pl-3">
                                                    @foreach($baseItem->components as $component)
                                                        @php
                                                            $totalConsumption = $item->quantity_requested * $component->pivot->quantity_required;
                                                            $componentStockStatus = $component->stock < $totalConsumption ? 'danger' : 'success';
                                                        @endphp
                                                        <li class="small text-muted d-flex justify-content-between">
                                                            <span>
                                                                &#x25B8; {{ $component->name }} (Requiere: {{ $component->pivot->quantity_required }}/Kit)
                                                            </span>
                                                            <span class="text-right">
                                                                **{{ $totalConsumption }}** {{ $component->unit->abbreviation ?? 'unid' }} | 
                                                                Stock: 
                                                                <span class="badge badge-{{ $componentStockStatus }}">{{ $component->stock }}</span>
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
                                    <td colspan="4" class="text-center">No hay ítems registrados para esta solicitud.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- --------------------------------- MODAL DE RECHAZO (Sin cambios) --------------------------------- --}}
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