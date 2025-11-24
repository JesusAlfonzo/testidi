@extends('adminlte::page')

@section('title', 'Auditoría del Sistema')

{{-- Plugins para DataTables --}}
@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugins', true)
@section('plugins.Responsive', true)

@section('content_header')
    <h1><i class="fas fa-history"></i> Auditoría y Logs del Sistema</h1>
@stop

@section('css')
    <style>
        /* Ajustes para DataTables Responsive */
        table.dataTable.dtr-inline.collapsed > tbody > tr > td:first-child:before, 
        table.dataTable.dtr-inline.collapsed > tbody > tr > th:first-child:before { left: 4px; }
        .table.dataTable.dtr-inline.collapsed > tbody > tr > td:first-child { padding-left: 10px !important; }
        
        /* Estilo para el JSON formateado en el modal */
        pre.json-box {
            background-color: #f4f6f9;
            border: 1px solid #dcdcdc;
            padding: 10px;
            border-radius: 5px;
            max-height: 300px;
            overflow-y: auto;
        }
    </style>
@stop

@section('content')
    <div class="card card-outline card-dark">
        <div class="card-header">
            <h3 class="card-title">Registro de Actividades (Últimos 1000 eventos)</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="auditTable" class="table table-striped table-bordered display nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th style="width: 15%">Fecha/Hora</th>
                            <th style="width: 15%">Usuario</th>
                            <th style="width: 10%">Acción</th>
                            <th style="width: 20%">Módulo / Entidad</th>
                            <th style="width: 10%">ID Afectado</th>
                            <th>Detalles</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($activities as $log)
                            @php
                                // Determinar color según la acción
                                $color = match($log->description) {
                                    'created' => 'success',
                                    'updated' => 'warning',
                                    'deleted' => 'danger',
                                    default => 'info',
                                };
                                
                                // Obtener nombre limpio del modelo (App\Models\Product -> Product)
                                $modelName = class_basename($log->subject_type);
                            @endphp
                            <tr>
                                <td data-order="{{ $log->created_at->timestamp }}">
                                    {{ $log->created_at->format('d/m/Y H:i:s') }}
                                </td>
                                <td>
                                    @if($log->causer)
                                        <strong>{{ $log->causer->name }}</strong>
                                        <br><small class="text-muted">{{ $log->causer->email }}</small>
                                    @else
                                        <span class="text-muted">Sistema / Automático</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-{{ $color }}">{{ strtoupper($log->description) }}</span>
                                </td>
                                <td>{{ $modelName }}</td>
                                <td>{{ $log->subject_id }}</td>
                                <td>
                                    {{-- Botón para ver cambios JSON --}}
                                    @if($log->properties->count() > 0)
                                        <button type="button" class="btn btn-xs btn-default text-primary view-changes-btn" 
                                            data-toggle="modal" 
                                            data-target="#modalChanges"
                                            data-attributes='{{ json_encode($log->properties['attributes'] ?? []) }}'
                                            data-old='{{ json_encode($log->properties['old'] ?? []) }}'>
                                            <i class="fas fa-eye"></i> Ver Cambios
                                        </button>
                                    @else
                                        <span class="text-muted text-xs">Sin detalles</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- MODAL PARA VER DETALLES --}}
    <div class="modal fade" id="modalChanges" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-dark">
                    <h5 class="modal-title"><i class="fas fa-code"></i> Detalle de Cambios</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fas fa-history text-warning"></i> Valor Anterior (Old)</h6>
                            <pre id="json-old" class="json-box text-xs">---</pre>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-check-circle text-success"></i> Valor Nuevo (New)</h6>
                            <pre id="json-attributes" class="json-box text-xs">---</pre>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Inicializar DataTable
            var table = $('#auditTable').DataTable({
                "responsive": true,
                "order": [[ 0, "desc" ]], // Ordenar por fecha descendente
                "language": { "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json" },
                "columnDefs": [
                    { "responsivePriority": 1, "targets": 0 }, // Fecha
                    { "responsivePriority": 2, "targets": 1 }, // Usuario
                    { "responsivePriority": 3, "targets": 2 }, // Acción
                ]
            });

            // Lógica del Modal para formatear JSON
            $('#modalChanges').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget); 
                var attributes = button.data('attributes'); 
                var old = button.data('old'); 

                var modal = $(this);
                
                // Formatear JSON bonito
                modal.find('#json-attributes').text(JSON.stringify(attributes, null, 4));
                
                if (jQuery.isEmptyObject(old)) {
                    modal.find('#json-old').text('No aplica / Creación inicial');
                } else {
                    modal.find('#json-old').text(JSON.stringify(old, null, 4));
                }
            });
        });
    </script>
@stop
