@extends('adminlte::page')

@section('title', 'Inventario | Solicitudes de Salida')

{{-- Plugins necesarios: DataTables y Responsive --}}
@section('plugins.Datatables', true) 
@section('plugins.DatatablesPlugins', true) 
@section('plugins.Responsive', true) 

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1><i class="fas fa-clipboard-list"></i> Solicitudes de Salida</h1>
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

            <div class="card card-outline card-info">
                <div class="card-body p-0">
                    {{-- 🔑 AJUSTE CRÍTICO: Usar table-responsive para forzar el ancho en contenedores --}}
                    <div class="table-responsive">
                        <table id="requestsTable" class="table table-striped table-bordered display nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    <th style="width: 10%">ID</th>
                                    <th style="width: 25%">Solicitante</th>
                                    <th style="width: 15%">Estado</th>
                                    <th style="width: 10%">Acciones</th>
                                    <th>Justificación</th>
                                    <th>Fecha Solicitud</th>
                                    <th>Aprobador</th>
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
                                        <td>
                                            <span class="badge badge-{{ $badgeColors[$request->status] ?? 'secondary' }}">
                                                {{ $request->status }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group" aria-label="Acciones de Solicitud">
                                                <a href="{{ route('admin.requests.show', $request) }}" class="btn btn-default text-info" title="Ver Detalle">
                                                    <i class="fas fa-eye"></i>
                                                </a>

                                                @if ($request->status === 'Pending' && Gate::allows('solicitudes_aprobar'))
                                                    <a href="{{ route('admin.requests.show', $request) }}" class="btn btn-default text-warning" title="Revisar y Aprobar/Rechazar">
                                                        <i class="fas fa-check-circle"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                        <td>{{ Str::limit($request->justification, 50) }}</td>
                                        <td data-order="{{ optional($request->requested_at)->timestamp }}">
                                            {{ optional($request->requested_at)->format('Y-m-d H:i') }}
                                        </td>
                                        <td>{{ $request->approver->name ?? '---' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No se encontraron solicitudes.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div> {{-- Cierre de table-responsive --}}
                </div>
            </div>
        </div>
    </div>
@stop

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
                "order": [[ 5, "desc" ]], // Ordenar por la columna Fecha Solicitud (índice 5)
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
                },
                "columnDefs": [
                    { "orderable": false, "targets": [3] }, // Acciones (ahora índice 3)
                    
                    // PRIORIDADES MÓVIL: Definimos qué se queda visible.
                    { "responsivePriority": 1, "targets": 0 }, // ID
                    { "responsivePriority": 2, "targets": 1 }, // Solicitante
                    { "responsivePriority": 3, "targets": 3 }, // Acciones
                    { "responsivePriority": 4, "targets": 2 }, // Estado
                    
                    // Ocultar completamente la Justificación y Aprobador en móvil 
                    { "responsivePriority": 100, "targets": [4, 6] }, 
                    
                    { "type": "date", "targets": 5 } // Fecha Solicitud (índice 5)
                ]
            });

            // 💡 FORZAR REDIBUJO AL CARGAR (Solución común a problemas de responsive en AdminLTE)
            // A veces, el CSS de AdminLTE interfiere con el cálculo inicial.
            setTimeout(function() {
                requestsTable.columns.adjust().responsive.recalc();
            }, 500);
        });
    </script>
@endsection