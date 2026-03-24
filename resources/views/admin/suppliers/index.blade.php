@extends('adminlte::page')

@section('title', 'Maestros | Proveedores')

{{-- Plugins necesarios --}}
@section('plugins.Datatables', true) 
@section('plugins.DatatablesPlugins', true) 
@section('plugins.Responsive', true) 

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1 class="m-0 text-dark"><i class="fas fa-truck"></i> Proveedores</h1>
        @can('proveedores_crear')
            <a href="{{ route('admin.suppliers.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Nuevo Proveedor
            </a>
        @endcan
    </div>
@stop

{{-- Estilos para corregir visualización --}}
@section('css')
    <style>
        /* Ajuste para el botón de expansión en móvil */
        table.dataTable.dtr-inline.collapsed > tbody > tr > td:first-child:before, 
        table.dataTable.dtr-inline.collapsed > tbody > tr > th:first-child:before { 
            left: 4px; 
            top: 50%;
            transform: translateY(-50%);
        }
        /* Quitar padding extra en PC */
        .table.dataTable.dtr-inline.collapsed > tbody > tr > td:first-child { 
            padding-left: 10px !important; 
        }
    </style>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            @include('admin.partials.session-messages')

            <div class="card card-outline card-info">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Listado de Proveedores</h3>
                        <div class="d-flex align-items-center">
                            <form method="GET" action="{{ route('admin.suppliers.index') }}" class="d-flex align-items-center">
                                <label for="statusFilter" class="mr-2 mb-0" style="font-size: 0.875rem;">Estado:</label>
                                <select name="status" id="statusFilter" class="form-control form-control-sm" style="width: auto;" onchange="this.form.submit()">
                                    <option value="all" {{ $status ?? 'all' === 'all' ? 'selected' : '' }}>Todos</option>
                                    <option value="active" {{ ($status ?? 'all') === 'active' ? 'selected' : '' }}>Activos</option>
                                    <option value="inactive" {{ ($status ?? 'all') === 'inactive' ? 'selected' : '' }}>Inactivos</option>
                                </select>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4">
                    <div class="table-responsive">
                        {{-- 🔑 ID 'suppliersTable', clases 'display nowrap' --}}
                        <table id="suppliersTable" class="table table-striped table-bordered display nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    {{-- Prioridad Alta (Visible en Móvil) --}}
                                    <th style="width: 5%">ID</th>
                                    <th style="width: 25%">Nombre</th>
                                    <th style="width: 10%">Estado</th>
                                    <th style="width: 15%">Acciones</th>
                                    
                                    {{-- Prioridad Baja (Oculto en Móvil) --}}
                                    <th style="width: 15%">Contacto / Teléfono</th>
                                    <th style="width: 15%">ID Fiscal</th>
                                    <th style="width: 15%">Email</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($suppliers as $supplier)
                                    <tr>
                                        <td>{{ $supplier->id }}</td>
                                        <td><strong>{{ $supplier->name }}</strong></td>
                                        <td>
                                            @if(isset($supplier->is_active) && $supplier->is_active)
                                                <span class="badge badge-success">Activo</span>
                                            @else
                                                <span class="badge badge-secondary">Inactivo</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                @can('proveedores_editar')
                                                    <a href="{{ route('admin.suppliers.edit', $supplier) }}" class="btn btn-default text-primary" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endcan

                                                @can('proveedores_eliminar')
                                                    <button type="button" class="btn btn-default text-danger" onclick="confirmDelete('{{ route('admin.suppliers.destroy', $supplier) }}', '{{ $supplier->name }}')" title="Eliminar">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endcan
                                            </div>
                                        </td>
                                        
                                        {{-- Columnas Secundarias --}}
                                        <td>
                                            {{ $supplier->contact_person }}
                                            @if($supplier->phone)
                                                <br><small class="text-muted"><i class="fas fa-phone-alt"></i> {{ $supplier->phone }}</small>
                                            @endif
                                        </td>
                                        <td><span class="badge badge-secondary">{{ $supplier->tax_id ?? 'N/A' }}</span></td>
                                        <td>
                                            @if($supplier->email)
                                                <a href="mailto:{{ $supplier->email }}">{{ $supplier->email }}</a>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    {{-- DataTables manejará el vacío --}}
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                {{-- Eliminamos la paginación manual --}}
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            const suppliersTable = $('#suppliersTable').DataTable({
                "responsive": true, 
                "paging": true, 
                "lengthChange": true, 
                "searching": true, 
                "ordering": true, 
                "info": true, 
                "autoWidth": false,
                "order": [[ 1, "asc" ]], // Ordenar por Nombre
                
                // 🔑 Traducción Nativa
                "language": {
                    "decimal": "",
                    "emptyTable": "No hay información disponible",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    "infoEmpty": "Mostrando 0 a 0 de 0 registros",
                    "infoFiltered": "(Filtrado de _MAX_ total registros)",
                    "infoPostFix": "",
                    "thousands": ",",
                    "lengthMenu": "Mostrar _MENU_ registros",
                    "loadingRecords": "Cargando...",
                    "processing": "Procesando...",
                    "search": "Buscar:",
                    "zeroRecords": "Sin resultados encontrados",
                    "paginate": {
                        "first": "Primero",
                        "last": "Último",
                        "next": "Siguiente",
                        "previous": "Anterior"
                    }
                },

                "columnDefs": [
                    { "orderable": false, "targets": [2] }, // Acciones no ordenables
                    
                    // 🔑 PRIORIDADES MÓVIL
                    { "responsivePriority": 1, "targets": 1 }, // Nombre
                    { "responsivePriority": 2, "targets": 2 }, // Acciones
                    { "responsivePriority": 3, "targets": 0 }, // ID
                    
                    // Ocultar el resto
                    { "responsivePriority": 100, "targets": [3, 4, 5] } 
                ]
            });
            
            // Ajuste de renderizado
            setTimeout(function() { suppliersTable.columns.adjust().responsive.recalc(); }, 500);
        });
    </script>
    @include('admin.partials.delete-confirm')
@endsection