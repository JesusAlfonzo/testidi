@extends('adminlte::page')

@section('title', 'Auditoría del Sistema')

@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugins', true)
@section('plugins.Responsive', true)
@section('plugins.Select2', true)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="text-dark font-weight-bold" style="font-size: 1.75rem;">
                <i class="fas fa-shield-alt text-primary mr-2"></i> Auditoría del Sistema
            </h1>
            <p class="text-muted mb-0">Historial completo de actividades y cambios en el sistema.</p>
        </div>
        <div>
            <a href="{{ route('admin.audit.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-sync-alt mr-1"></i> Recargar
            </a>
        </div>
    </div>
@stop

@section('css')
    <style>
        .card-custom { border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06); border: none; margin-bottom: 2rem; }
        .filter-section { background: #ffffff; border-radius: 12px; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }

        #auditTable { border-collapse: separate !important; border-spacing: 0 6px !important; width: 100% !important; }
        #auditTable thead th {
            font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700;
            color: #4b5563; background-color: #f9fafb; border: none; padding: 10px 14px;
        }
        #auditTable tbody tr {
            background-color: #ffffff !important; box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            transition: all 0.2s ease; border-radius: 8px;
        }
        #auditTable tbody tr:hover {
            background-color: #f9fafb !important; transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.07);
        }
        #auditTable tbody td { padding: 12px 14px; border: none !important; vertical-align: middle; font-size: 0.85rem; color: #1f2937; }
        #auditTable tbody tr td:first-child { border-top-left-radius: 8px; border-bottom-left-radius: 8px; }
        #auditTable tbody tr td:last-child { border-top-right-radius: 8px; border-bottom-right-radius: 8px; }

        .avatar-circle {
            width: 32px; height: 32px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.7rem; font-weight: 700; color: #fff; flex-shrink: 0;
        }
        .badge-audit { padding: 0.4em 0.75em; font-weight: 600; font-size: 0.7rem; border-radius: 6px; }
        .id-monospace { font-family: 'Courier New', monospace; font-size: 0.75rem; color: #9ca3af; font-weight: 600; }

        table.dataTable.dtr-inline.collapsed > tbody > tr > td:first-child:before,
        table.dataTable.dtr-inline.collapsed > tbody > tr > th:first-child:before { left: 4px; top: 50%; transform: translateY(-50%); }
        .table.dataTable.dtr-inline.collapsed > tbody > tr > td:first-child { padding-left: 32px !important; }
    </style>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            @include('admin.partials.session-messages')

            {{-- FILTROS --}}
            <div class="card filter-section p-3 mb-3">
                <form method="GET" action="{{ route('admin.audit.index') }}" class="row align-items-end">
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label class="text-xs font-weight-bold text-secondary text-uppercase mb-1 d-block">
                            <i class="fas fa-user mr-1"></i> Usuario
                        </label>
                        <select name="causer_id" class="form-control select2" style="border-radius: 8px; width: 100%">
                            <option value="">Todos los usuarios</option>
                            @foreach($users as $id => $name)
                                <option value="{{ $id }}" {{ request('causer_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2 mb-md-0">
                        <label class="text-xs font-weight-bold text-secondary text-uppercase mb-1 d-block">
                            <i class="fas fa-bolt mr-1"></i> Acción
                        </label>
                        <select name="action_type" class="form-control" style="border-radius: 8px;">
                            <option value="">Todas</option>
                            <option value="created" {{ request('action_type') == 'created' ? 'selected' : '' }}>Creaciones</option>
                            <option value="updated" {{ request('action_type') == 'updated' ? 'selected' : '' }}>Actualizaciones</option>
                            <option value="deleted" {{ request('action_type') == 'deleted' ? 'selected' : '' }}>Eliminaciones</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label class="text-xs font-weight-bold text-secondary text-uppercase mb-1 d-block">
                            <i class="fas fa-cube mr-1"></i> Módulo
                        </label>
                        <select name="subject_type" class="form-control select2" style="border-radius: 8px; width: 100%">
                            <option value="">Todos los módulos</option>
                            @foreach($subjects as $class => $label)
                                <option value="{{ $class }}" {{ request('subject_type') == $class ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2 mb-md-0">
                        <label class="text-xs font-weight-bold text-secondary text-uppercase mb-1 d-block">
                            <i class="fas fa-calendar-alt mr-1"></i> Desde
                        </label>
                        <input type="date" name="date_from" class="form-control" style="border-radius: 8px;" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2 mb-2 mb-md-0">
                        <label class="text-xs font-weight-bold text-secondary text-uppercase mb-1 d-block">
                            <i class="fas fa-calendar-alt mr-1"></i> Hasta
                        </label>
                        <input type="date" name="date_to" class="form-control" style="border-radius: 8px;" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-12 mt-2">
                        <div class="d-flex justify-content-end">
                            <a href="{{ route('admin.audit.index') }}" class="btn btn-outline-secondary font-weight-bold mr-2" style="border-radius: 8px;">
                                <i class="fas fa-undo mr-1"></i> Resetear Filtros
                            </a>
                            <button type="submit" class="btn btn-primary font-weight-bold" style="border-radius: 8px;">
                                <i class="fas fa-search mr-1"></i> Buscar
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- TABLA DE AUDITORÍA --}}
            <div class="card card-custom p-3 bg-white">
                <div class="d-flex justify-content-between align-items-center px-1 mb-3">
                    <div>
                        <span class="font-weight-bold text-dark">Registro de Actividades</span>
                        <span class="badge badge-light border ml-2">{{ $activities->total() }} registros</span>
                    </div>
                    <div>
                        <select name="per_page" class="form-control form-control-sm" style="border-radius: 6px; width: auto; display: inline-block;" onchange="window.location.href='{{ route('admin.audit.index') }}?per_page='+this.value+'&{{ http_build_query(request()->except('per_page', 'page')) }}'">
                            <option value="15" {{ $perPage == 15 ? 'selected' : '' }}>15</option>
                            <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="auditTable" class="table table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th style="width: 5%">ID</th>
                                <th style="width: 22%">Usuario / Fecha</th>
                                <th style="width: 13%">Acción</th>
                                <th style="width: 22%">Módulo / Registro</th>
                                <th style="width: 10%">Detalles</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($activities as $log)
                                <tr>
                                    <td class="align-middle text-center">
                                        <span class="id-monospace">#{{ $log->id }}</span>
                                    </td>
                                    <td class="align-middle">
                                        <div class="d-flex align-items-center">
                                            @php
                                                $colors = ['#4f46e5','#dc2626','#059669','#d97706','#7c3aed','#db2777','#2563eb','#0891b2'];
                                                $colorIdx = $log->causer ? abs(crc32($log->causer->name)) % count($colors) : 0;
                                            @endphp
                                            <div class="avatar-circle mr-2" style="background-color: {{ $colors[$colorIdx] }};">
                                                {{ $log->causer_initials }}
                                            </div>
                                            <div>
                                                <div class="font-weight-bold" style="font-size: 0.85rem;">
                                                    {{ $log->causer->name ?? 'Sistema / Automático' }}
                                                </div>
                                                <div style="font-size: 0.7rem; color: #9ca3af;">
                                                    <i class="far fa-clock mr-1"></i>{{ $log->created_at->format('d-m-Y h:i A') }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge badge-audit badge-{{ $log->action_badge }}">{{ $log->human_action }}</span>
                                        @if($log->description === 'deleted')
                                            <i class="fas fa-trash-alt text-danger ml-1" style="font-size: 0.7rem;"></i>
                                        @elseif($log->description === 'created')
                                            <i class="fas fa-plus-circle text-success ml-1" style="font-size: 0.7rem;"></i>
                                        @elseif($log->description === 'updated')
                                            <i class="fas fa-edit text-info ml-1" style="font-size: 0.7rem;"></i>
                                        @endif
                                        <div style="font-size: 0.7rem; color: #9ca3af; margin-top: 2px;">{{ $log->description }}</div>
                                    </td>
                                    <td class="align-middle">
                                        <div class="font-weight-bold" style="font-size: 0.85rem; color: #374151;">{{ $log->module_name }}</div>
                                        <div style="font-size: 0.78rem; color: #6b7280;">
                                            <i class="fas fa-tag mr-1" style="font-size: 0.6rem;"></i>
                                            {{ $log->subject_name }}
                                        </div>
                                    </td>
                                    <td class="align-middle text-center">
                                        @if($log->properties && $log->properties->count() > 0)
                                            <a href="{{ route('admin.audit.show', $log) }}" class="btn btn-sm btn-outline-primary font-weight-bold" style="border-radius: 6px; font-size: 0.75rem;">
                                                <i class="fas fa-eye mr-1"></i> Ver Cambios
                                            </a>
                                        @else
                                            <span class="text-muted" style="font-size: 0.75rem;">Sin cambios</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                        No se encontraron registros de actividad con los filtros aplicados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center mt-3">
                    {{ $activities->links() }}
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('.select2').select2({ theme: 'bootstrap4', width: '100%' });

            $(document).on('select2:open', function() {
                setTimeout(function() {
                    var dropdown = document.querySelector('.select2-dropdown');
                    if (dropdown) {
                        dropdown.style.maxHeight = '350px';
                        dropdown.style.overflow = 'hidden';
                        var results = dropdown.querySelector('.select2-results');
                        if (results) {
                            results.style.maxHeight = '350px';
                            results.style.overflowY = 'auto';
                        }
                    }
                }, 10);
            });

            var auditTable = $('#auditTable').DataTable({
                "responsive": true,
                "paging": false,
                "lengthChange": false,
                "searching": true,
                "ordering": true,
                "info": false,
                "autoWidth": false,
                "order": [[0, 'desc']],
                "language": {
                    "decimal": "", "emptyTable": "No hay información disponible", "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    "infoEmpty": "Mostrando 0 a 0 de 0 registros", "infoFiltered": "(Filtrado de _MAX_ total registros)",
                    "thousands": ",", "lengthMenu": "Mostrar _MENU_ registros", "loadingRecords": "Cargando...",
                    "processing": "Procesando...", "search": "Buscar:", "zeroRecords": "Sin resultados encontrados",
                    "paginate": { "first": "Primero", "last": "Último", "next": "Siguiente", "previous": "Anterior" }
                },
                "columnDefs": [
                    { "responsivePriority": 1, "targets": 1 },
                    { "responsivePriority": 2, "targets": 2 },
                    { "responsivePriority": 3, "targets": 4 },
                    { "orderable": false, "targets": 4 }
                ]
            });

            setTimeout(function() {
                auditTable.columns.adjust().responsive.recalc();
            }, 500);
        });
    </script>
@stop
