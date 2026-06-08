@extends('adminlte::page')

@section('title', 'Detalle de Auditoría #' . $log->id)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="text-dark font-weight-bold" style="font-size: 1.75rem;">
                <i class="fas fa-search text-primary mr-2"></i> Detalle de Cambio
            </h1>
            <p class="text-muted mb-0">Inspección forense del registro de actividad #{{ $log->id }}.</p>
        </div>
        <div>
            <a href="{{ route('admin.audit.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Volver al Listado
            </a>
        </div>
    </div>
@stop

@section('css')
    <style>
        .card-custom { border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06); border: none; margin-bottom: 2rem; }

        .diff-table { width: 100%; border-collapse: separate; border-spacing: 0 4px; }
        .diff-table th { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; color: #4b5563; padding: 8px 12px; border: none; }
        .diff-table td { padding: 10px 12px; border: none !important; vertical-align: middle; font-size: 0.85rem; }

        .diff-old, .diff-new { padding: 8px 12px; border-radius: 6px; font-weight: 600; display: inline-block; }
        .diff-old { background-color: #fef2f2; color: #991b1b; text-decoration: line-through; }
        .diff-new { background-color: #f0fdf4; color: #166534; }
        .field-label { font-weight: 600; color: #374151; font-size: 0.8rem; }

        .meta-table { width: 100%; }
        .meta-table td { padding: 8px 12px; border: none; font-size: 0.85rem; vertical-align: top; }
        .meta-table td:first-child { font-weight: 600; color: #6b7280; width: 140px; }

        .avatar-circle {
            width: 36px; height: 36px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.75rem; font-weight: 700; color: #fff; flex-shrink: 0;
        }
        .badge-field { padding: 0.35em 0.65em; font-weight: 600; font-size: 0.65rem; border-radius: 4px; }
    </style>
@stop

@section('content')
    <div class="row">
        {{-- COLUMNA IZQUIERDA: Metadatos del Evento --}}
        <div class="col-lg-4 col-md-12">
            <div class="card card-custom p-3 bg-white mb-4">
                <h5 class="font-weight-bold text-dark mb-3">
                    <i class="fas fa-info-circle text-primary mr-1"></i> Metadatos del Evento
                </h5>
                <table class="meta-table">
                    <tr>
                        <td><i class="fas fa-hashtag text-muted mr-1"></i> ID Evento:</td>
                        <td class="font-weight-bold">#{{ $log->id }}</td>
                    </tr>
                    <tr>
                        <td><i class="fas fa-calendar text-muted mr-1"></i> Fecha/Hora:</td>
                        <td class="font-weight-bold">{{ $log->created_at->format('d-m-Y h:i A') }}</td>
                    </tr>
                    <tr>
                        <td><i class="fas fa-user text-muted mr-1"></i> Responsable:</td>
                        <td>
                            <div class="d-flex align-items-center">
                                @php
                                    $colors = ['#4f46e5','#dc2626','#059669','#d97706','#7c3aed','#db2777','#2563eb','#0891b2'];
                                    $colorIdx = $log->causer ? abs(crc32($log->causer->name)) % count($colors) : 0;
                                @endphp
                                <div class="avatar-circle mr-2" style="background-color: {{ $colors[$colorIdx] }};">
                                    {{ $log->causer_initials }}
                                </div>
                                <div>
                                    <span class="font-weight-bold">{{ $log->causer->name ?? 'Sistema' }}</span>
                                    @if($log->causer)
                                        <br><small class="text-muted">{{ $log->causer->email }}</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><i class="fas fa-bolt text-muted mr-1"></i> Acción:</td>
                        <td>
                            <span class="badge badge-field badge-{{ $log->action_badge }}">{{ $log->human_action }}</span>
                            <small class="text-muted ml-1">({{ $log->description }})</small>
                        </td>
                    </tr>
                    <tr>
                        <td><i class="fas fa-cube text-muted mr-1"></i> Módulo:</td>
                        <td class="font-weight-bold">{{ $log->module_name }}</td>
                    </tr>
                    <tr>
                        <td><i class="fas fa-tag text-muted mr-1"></i> Registro:</td>
                        <td>
                            <span class="font-weight-bold">{{ $log->subject_name }}</span>
                            <br><small class="text-muted">ID: {{ $log->subject_id }} | {{ class_basename($log->subject_type) }}</small>
                        </td>
                    </tr>
                </table>
            </div>

            @if($log->description === 'deleted')
                <div class="card card-custom p-3 bg-white mb-4 border-left border-danger" style="border-left-width: 4px !important;">
                    <div class="d-flex align-items-center">
                        <div class="mr-3 text-danger">
                            <i class="fas fa-trash-alt fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="font-weight-bold text-danger mb-1">Registro Eliminado</h6>
                            <p class="text-muted small mb-0">Este registro fue eliminado del sistema. Los datos mostrados son los valores que tenía al momento de la eliminación.</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- COLUMNA DERECHA: Diff de Cambios --}}
        <div class="col-lg-8 col-md-12">
            @php $changes = $log->properties_diff; @endphp

            @if($log->description === 'created' && empty($changes))
                <div class="card card-custom p-4 bg-white mb-4 text-center">
                    <i class="fas fa-plus-circle text-success fa-3x mb-3"></i>
                    <h5 class="font-weight-bold text-dark">Registro Creado</h5>
                    <p class="text-muted mb-0">Este es un evento de creación. No hay valores anteriores para comparar.</p>
                    @if($log->properties && $log->properties->count() > 0)
                        @php $attrs = $log->properties['attributes'] ?? []; @endphp
                        @if(!empty($attrs))
                            <hr>
                            <h6 class="text-left font-weight-bold text-dark mb-3"><i class="fas fa-check-circle text-success mr-1"></i> Valores Iniciales</h6>
                            <div class="table-responsive text-left">
                                <table class="diff-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 35%;">Campo</th>
                                            <th>Valor Asignado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($attrs as $key => $value)
                                            @if(in_array($key, ['id', 'created_at', 'updated_at'])) @continue @endif
                                            <tr>
                                                <td><span class="field-label">{{ \App\Models\Activity::fieldLabel($key) }}</span></td>
                                                <td><span class="diff-new">{!! \App\Models\Activity::fieldValue($key, $value) !!}</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    @endif
                </div>
            @elseif($log->description === 'deleted')
                <div class="card card-custom p-4 bg-white mb-4">
                    <h5 class="font-weight-bold text-dark mb-3"><i class="fas fa-history text-danger mr-1"></i> Valores al Momento de Eliminar</h5>
                    @php $attrs = $log->properties['attributes'] ?? []; @endphp
                    @if(!empty($attrs))
                        <div class="table-responsive">
                            <table class="diff-table">
                                <thead>
                                    <tr>
                                        <th style="width: 35%;">Campo</th>
                                        <th>Último Valor Conocido</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($attrs as $key => $value)
                                        @if(in_array($key, ['id', 'updated_at'])) @continue @endif
                                        <tr>
                                            <td><span class="field-label">{{ \App\Models\Activity::fieldLabel($key) }}</span></td>
                                            <td><span class="diff-old">{!! \App\Models\Activity::fieldValue($key, $value) !!}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0 text-center py-3">No hay datos disponibles del registro eliminado.</p>
                    @endif
                </div>
            @elseif(!empty($changes))
                {{-- TARJETA DE RESUMEN --}}
                <div class="card card-custom p-3 bg-white mb-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="font-weight-bold text-dark mb-1">
                                <i class="fas fa-code-branch text-primary mr-1"></i> Cambios Detectados
                            </h5>
                            <p class="text-muted small mb-0">
                                Se modificaron <strong>{{ count($changes) }}</strong> campo(s) en este registro.
                            </p>
                        </div>
                        <div class="text-right">
                            <span class="badge badge-light border mr-2">
                                <span class="diff-old d-inline px-2 py-1" style="font-size: 0.7rem;">Antes</span>
                            </span>
                            <span class="badge badge-light border">
                                <span class="diff-new d-inline px-2 py-1" style="font-size: 0.7rem;">Después</span>
                            </span>
                        </div>
                    </div>
                </div>

                {{-- TABLA DE COMPARACIÓN --}}
                <div class="card card-custom p-3 bg-white mb-4">
                    <div class="table-responsive">
                        <table class="diff-table">
                            <thead>
                                <tr>
                                    <th style="width: 28%;">Campo</th>
                                    <th style="width: 36%;">Valor Anterior</th>
                                    <th style="width: 36%;">Valor Nuevo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($changes as $change)
                                    <tr>
                                        <td class="align-middle">
                                            <span class="field-label">{{ $change['label'] }}</span>
                                            <br><small class="text-muted" style="font-size: 0.65rem;">{{ $change['field'] }}</small>
                                        </td>
                                        <td class="align-middle">
                                            <span class="diff-old">{!! $change['old_html'] !!}</span>
                                        </td>
                                        <td class="align-middle">
                                            <span class="diff-new">{!! $change['new_html'] !!}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="card card-custom p-4 bg-white mb-4 text-center">
                    <i class="fas fa-info-circle text-muted fa-3x mb-3"></i>
                    <h5 class="font-weight-bold text-dark">Sin Detalles de Cambio</h5>
                    <p class="text-muted mb-0">Este registro no contiene información detallada de los campos modificados.</p>
                </div>
            @endif

            {{-- INFORMACIÓN ADICIONAL --}}
            @if($log->batch_uuid)
                <div class="card card-custom p-3 bg-white mb-4">
                    <h6 class="font-weight-bold text-dark mb-2"><i class="fas fa-layer-group text-muted mr-1"></i> Información Técnica</h6>
                    <table class="meta-table">
                        <tr>
                            <td>UUID de Lote:</td>
                            <td><code class="small">{{ $log->batch_uuid }}</code></td>
                        </tr>
                        <tr>
                            <td>Log Name:</td>
                            <td><code class="small">{{ $log->log_name ?? 'default' }}</code></td>
                        </tr>
                    </table>
                </div>
            @endif
        </div>
    </div>
@stop
