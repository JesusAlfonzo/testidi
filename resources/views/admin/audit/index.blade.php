@extends('adminlte::page')

@section('title', 'Auditoría del Sistema')

{{-- Plugins --}}
@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugins', true)
@section('plugins.Responsive', true)
@section('plugins.Select2', true)

@section('content_header')
    <h1><i class="fas fa-history"></i> Auditoría y Logs del Sistema</h1>
@stop

@section('css')
    <style>
        /* Ajustes para DataTables Responsive */
        table.dataTable.dtr-inline.collapsed > tbody > tr > td:first-child:before, 
        table.dataTable.dtr-inline.collapsed > tbody > tr > th:first-child:before { left: 4px; }
        .table.dataTable.dtr-inline.collapsed > tbody > tr > td:first-child { padding-left: 10px !important; }
        
        pre.json-box {
            background-color: #f4f6f9;
            border: 1px solid #dcdcdc;
            padding: 10px;
            border-radius: 5px;
            max-height: 300px;
            overflow-y: auto;
            font-size: 0.85rem;
        }
    </style>
@stop

@section('content')
    
    {{-- 🔎 FILTROS DE BÚSQUEDA --}}
    <div class="card card-outline card-primary collapsed-card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter"></i> Filtros Avanzados</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.audit.index') }}">
                <div class="row">
                    {{-- Usuario --}}
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Usuario Responsable</label>
                            <select name="causer_id" class="form-control select2">
                                <option value="">Todos</option>
                                @foreach($users as $id => $name)
                                    <option value="{{ $id }}" {{ request('causer_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Acción --}}
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Acción</label>
                            <select name="action_type" class="form-control select2">
                                <option value="">Todas</option>
                                <option value="created" {{ request('action_type') == 'created' ? 'selected' : '' }}>Creación (Created)</option>
                                <option value="updated" {{ request('action_type') == 'updated' ? 'selected' : '' }}>Edición (Updated)</option>
                                <option value="deleted" {{ request('action_type') == 'deleted' ? 'selected' : '' }}>Eliminación (Deleted)</option>
                            </select>
                        </div>
                    </div>

                    {{-- Modelo/Módulo --}}
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Módulo Afectado</label>
                            <select name="subject_type" class="form-control select2">
                                <option value="">Todos</option>
                                @foreach($subjects as $class => $label)
                                    <option value="{{ $class }}" {{ request('subject_type') == $class ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Fechas --}}
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Desde</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Hasta</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12 text-right">
                        <a href="{{ route('admin.audit.index') }}" class="btn btn-default mr-2">Limpiar</a>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Buscar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- TABLA DE RESULTADOS --}}
    <div class="card card-outline card-dark">
        <div class="card-header">
            <h3 class="card-title">Registro de Actividades</h3>
        </div>
        <div class="card-body p-4">
            <div class="table-responsive">
                <table id="auditTable" class="table table-striped table-bordered display nowrap" style="width:100%">
                    <thead>
                        <tr>
                            <th style="width: 15%">Fecha/Hora</th>
                            <th style="width: 15%">Usuario</th>
                            <th style="width: 10%">Acción</th>
                            <th style="width: 20%">Módulo / Entidad</th>
                            <th style="width: 10%">ID Ref.</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($activities as $log)
                            @php
                                $color = match($log->description) {
                                    'created' => 'success',
                                    'updated' => 'warning',
                                    'deleted' => 'danger',
                                    default => 'info',
                                };
                                
                                $modelLabel = $subjects[$log->subject_type] ?? class_basename($log->subject_type);
                            @endphp
                            <tr>
                                <td data-order="{{ optional($log->created_at)->timestamp }}">
                                    {{ optional($log->created_at)->format('d/m/Y H:i:s') }}
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
                                <td>{{ $modelLabel }}</td>
                                <td>{{ $log->subject_id }}</td>
                                <td>
                                    @if($log->properties && $log->properties->count() > 0)
                                        <button type="button" class="btn btn-xs btn-default text-primary view-changes-btn" 
                                            data-toggle="modal" 
                                            data-target="#modalChanges"
                                            data-attributes="{{ json_encode($log->properties['attributes'] ?? []) }}"
                                            data-old="{{ json_encode($log->properties['old'] ?? []) }}">
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
        {{-- 🔑 Nota: Hemos quitado la paginación de Laravel aquí para que no se duplique con la de DataTables --}}
        {{-- Si necesitas ver más registros, aumenta el paginate() en el controlador. --}}
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
            // Inicializar Select2
            $('.select2').select2({
                theme: 'bootstrap4',
                width: '100%'
            });

            // Inicializar DataTable
            const auditTable = $('#auditTable').DataTable({
                "responsive": true,
                "paging": true, // 🔑 Activado: Paginación de DataTables
                "lengthChange": true, // 🔑 Activado: Selector de cantidad de registros
                "searching": true, // 🔑 Activado: Buscador rápido (en los resultados actuales)
                "ordering": true,
                "info": true, // 🔑 Activado: "Mostrando X de Y"
                "autoWidth": false,
                "order": [[ 0, "desc" ]], 
                
                // Traducción Nativa
                "language": {
                    "decimal": "", "emptyTable": "No hay información disponible", "info": "Mostrando _START_ a _END_ de _TOTAL_ registros", 
                    "infoEmpty": "Mostrando 0 a 0 de 0 registros", "infoFiltered": "(Filtrado de _MAX_ total registros)", "infoPostFix": "", 
                    "thousands": ",", "lengthMenu": "Mostrar _MENU_ registros", "loadingRecords": "Cargando...", "processing": "Procesando...", 
                    "search": "Buscar:", "zeroRecords": "Sin resultados encontrados", 
                    "paginate": { "first": "Primero", "last": "Último", "next": "Siguiente", "previous": "Anterior" }
                },
                "columnDefs": [
                    { "responsivePriority": 1, "targets": 0 }, // Fecha
                    { "responsivePriority": 2, "targets": 2 }, // Acción
                    { "responsivePriority": 3, "targets": 5 }, // Botón Ver
                    { "orderable": false, "targets": 5 }      // Desactivar orden en acciones
                ]
            });

            // Forzar Redibujo
            setTimeout(function() {
                auditTable.columns.adjust().responsive.recalc();
            }, 500);

            // Lógica del Modal
            $('#modalChanges').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget); 
                var attributes = button.data('attributes'); 
                var old = button.data('old'); 
                var modal = $(this);
                
                // Convertir a objeto si viene como string
                if (typeof attributes === 'string') { try { attributes = JSON.parse(attributes); } catch(e) {} }
                if (typeof old === 'string') { try { old = JSON.parse(old); } catch(e) {} }
                
                modal.find('#json-attributes').text(JSON.stringify(attributes, null, 4));
                
                if ($.isEmptyObject(old)) {
                    modal.find('#json-old').text('No aplica / Creación inicial');
                } else {
                    modal.find('#json-old').text(JSON.stringify(old, null, 4));
                }
            });
        });
    </script>
@stop