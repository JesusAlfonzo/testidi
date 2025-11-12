@extends('adminlte::page')

@section('title', 'Maestros | Proveedores')

{{-- Plugins necesarios: DataTables, Plugins y Responsive --}}
@section('plugins.Datatables', true) 
@section('plugins.DatatablesPlugins', true) 
@section('plugins.Responsive', true) 

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1><i class="fas fa-truck"></i> Proveedores</h1>
        @can('proveedores_crear')
            <a href="{{ route('admin.suppliers.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Nuevo Proveedor
            </a>
        @endcan
    </div>
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
    <div class="row">
        <div class="col-12">
            @include('admin.partials.session-messages')

            <div class="card card-outline card-info">
                <div class="card-body p-0">
                    {{-- table-responsive y clases de Datatables --}}
                    <div class="table-responsive">
                        <table id="suppliersTable" class="table table-striped table-bordered display nowrap">
                            <thead>
                                <tr>
                                    {{-- Prioridades Altas --}}
                                    <th style="width: 5%">ID</th>
                                    <th style="width: 25%">Nombre</th>
                                    <th style="width: 20%">Contacto / Teléfono</th>
                                    <th style="width: 15%">Acciones</th>
                                    
                                    {{-- Prioridades Bajas --}}
                                    <th style="width: 15%">ID Fiscal / RUC</th>
                                    <th style="width: 20%">Email</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($suppliers as $supplier)
                                    <tr>
                                        <td>{{ $supplier->id }}</td>
                                        <td><strong>{{ $supplier->name }}</strong></td>
                                        <td>
                                            {{ $supplier->contact_person }}
                                            @if($supplier->phone)
                                                <br>
                                                <small class="text-muted"><i class="fas fa-phone-alt"></i> {{ $supplier->phone }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group" aria-label="Acciones de Proveedor">
                                                @can('proveedores_editar')
                                                    <a href="{{ route('admin.suppliers.edit', $supplier) }}" class="btn btn-default text-primary" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endcan

                                                @can('proveedores_eliminar')
                                                    <form action="{{ route('admin.suppliers.destroy', $supplier) }}" method="POST" style="display:inline-block;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-default text-danger" title="Eliminar" onclick="return confirm('⚠️ ADVERTENCIA: ¿Estás seguro de que deseas eliminar este proveedor? Esto podría afectar a sus compras registradas.')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </td>
                                        {{-- Columnas Ocultas en Móvil --}}
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
                                    <tr>
                                        <td colspan="6" class="text-center">No se encontraron proveedores registrados.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div> {{-- Cierre de table-responsive --}}
                </div>
                {{-- Eliminamos el card-footer con paginación de Laravel --}}
            </div>
        </div>
    </div>
@stop

{{-- ---------------------------------------------------- --}}
{{-- Sección de Scripts para Inicializar DataTables --}}
{{-- ---------------------------------------------------- --}}
@section('js')
    <script>
        $(document).ready(function() {
            // Inicializar Datatables
            const suppliersTable = $('#suppliersTable').DataTable({
                "responsive": true, 
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false, 
                "order": [[ 1, "asc" ]], // Ordenar por la columna Nombre (índice 1) ascendente
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
                },
                "columnDefs": [
                    { "orderable": false, "targets": [3] }, // Acciones (índice 3)

                    // 🔑 PRIORIDADES MÓVIL:
                    { "responsivePriority": 1, "targets": 1 }, // Nombre
                    { "responsivePriority": 2, "targets": 3 }, // Acciones
                    { "responsivePriority": 3, "targets": 2 }, // Contacto/Teléfono
                    { "responsivePriority": 4, "targets": 0 }, // ID
                    
                    // 🔑 Bajas prioridades: Se ocultan primero
                    { "responsivePriority": 100, "targets": [4, 5] } // ID Fiscal / RUC, Email
                ]
            });
            
            // Forzar Redibujo para corregir renderizado inicial
            setTimeout(function() {
                suppliersTable.columns.adjust().responsive.recalc();
            }, 500);
        });
    </script>
@endsection