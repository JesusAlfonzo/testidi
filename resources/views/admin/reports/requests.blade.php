@extends('adminlte::page')

@section('title', 'Reporte de Solicitudes de Inventario')

{{-- Plugins necesarios: DataTables, Plugins y Responsive --}}
@section('plugins.Datatables', true) 
@section('plugins.DatatablesPlugins', true) 
@section('plugins.Responsive', true) 

@section('content_header')
    <h1><i class="fas fa-list-alt"></i> Reporte de Solicitudes (Movimientos de Salida)</h1>
@stop

{{-- 🔑 AJUSTE CRÍTICO EN EL CSS (Para eliminar el padding extra del Responsive en PC) --}}
@section('css')
    <style>
        /* Ajusta la posición del botón de expansión (+) */
        table.dataTable.dtr-inline.collapsed > tbody > tr > td:first-child:before, 
        table.dataTable.dtr-inline.collapsed > tbody > tr > th:first-child:before {
            left: 4px; 
        }
        
        /* Elimina el padding izquierdo de la primera columna para evitar el scroll horizontal en PC */
        .table.dataTable.dtr-inline.collapsed > tbody > tr > td:first-child, 
        .table.dataTable.dtr-inline.collapsed > tbody > tr > th:first-child {
            padding-left: 10px !important; 
        }
    </style>
@stop

@section('content')
    {{-- Línea 32 corregida (se eliminó el caracter invisible NBSP antes de @php) --}}
    @php
        // Definición de colores de estado para las insignias (badges)
        $badgeColors = [
            'Pending' => 'warning',
            'Approved' => 'success',
            'Rejected' => 'danger',
            'Finalized' => 'primary', // Asumiendo que puede haber un estado finalizado
        ];
    @endphp

    <div class="card card-outline card-info">
        <div class="card-header">
            <h3 class="card-title">Histórico de Solicitudes de Salida</h3>
            <div class="card-tools">
                <a href="#" class="btn btn-tool btn-sm disabled">
                    <i class="fas fa-download"></i> Exportar a CSV (Próximamente con Datatables)
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            {{-- table-responsive y clases de Datatables --}}
            <div class="table-responsive">
                <table id="requestsTable" class="table table-striped table-bordered display nowrap">
                    <thead>
                        <tr>
                            {{-- Prioridades Altas --}}
                            <th style="width: 10%">ID</th>
                            <th style="width: 15%">Estado</th>
                            <th style="width: 15%">Fecha Solicitud</th>
                            <th style="width: 5%">Detalle</th>
                            
                            {{-- Prioridades Bajas --}}
                            <th style="width: 20%">Solicitante</th>
                            <th>Justificación</th>
                            <th style="width: 15%">Procesado por</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($requests as $request)
                            @php
                                $badge = $badgeColors[$request->status] ?? 'secondary';
                            @endphp
                            <tr>
                                <td>REQ-{{ $request->id }}</td>
                                <td>
                                    <span class="badge badge-{{ $badge }}">{{ $request->status }}</span>
                                </td>
                                <td data-order="{{ $request->requested_at->timestamp }}">
                                    {{ $request->requested_at->format('Y-m-d H:i') }}
                                </td>
                                <td>
                                    <a href="{{ route('admin.requests.show', $request->id) }}" class="btn btn-xs btn-info" title="Ver Detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                                {{-- Datos Ocultables --}}
                                <td>{{ $request->requester->name ?? 'N/A' }}</td>
                                <td>{{ Str::limit($request->justification, 50) }}</td>
                                <td>{{ $request->approver->name ?? 'Pendiente' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No se encontraron solicitudes de inventario.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div> {{-- Cierre de table-responsive --}}
        </div>
        {{-- Se elimina el card-footer con paginación de Laravel --}}
    </div>
@stop

{{-- ---------------------------------------------------- --}}
{{-- Sección de Scripts para Inicializar DataTables --}}
{{-- ---------------------------------------------------- --}}
@section('js')
    <script>
        $(document).ready(function() {
            // Inicializar Datatables
            const requestsTable = $('#requestsTable').DataTable({
                "responsive": true, 
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false, 
                "order": [[ 2, "desc" ]], // Ordenar por Fecha Solicitud (índice 2) descendente
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
                },
                "columnDefs": [
                    { "orderable": false, "targets": [3] }, // Detalle (índice 3)
                    { "type": "date", "targets": 2 }, // Fecha Solicitud (índice 2)
                    
                    // 🔑 PRIORIDADES MÓVIL:
                    { "responsivePriority": 1, "targets": 1 }, // Estado
                    { "responsivePriority": 2, "targets": 3 }, // Detalle
                    { "responsivePriority": 3, "targets": 2 }, // Fecha Solicitud
                    { "responsivePriority": 4, "targets": 0 }, // ID (REQ-XXX)
                    
                    // 🔑 Bajas prioridades: Se ocultan primero
                    { "responsivePriority": 100, "targets": [4, 5, 6] } // Solicitante, Justificación, Procesado por
                ]
            });
            
            // Forzar Redibujo para corregir renderizado inicial
            setTimeout(function() {
                requestsTable.columns.adjust().responsive.recalc();
            }, 500);
        });
    </script>
@endsection