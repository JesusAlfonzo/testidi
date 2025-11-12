@extends('adminlte::page')

@section('title', 'Inventario | Productos')

{{-- Plugins necesarios: DataTables, Plugins y Responsive --}}
@section('plugins.Datatables', true) 
@section('plugins.DatatablesPlugins', true) 
@section('plugins.Responsive', true) 

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1><i class="fas fa-box-open"></i> Listado de Productos</h1>
        @can('productos_crear')
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Agregar Producto
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
                    {{-- 🔑 table-responsive y clases de Datatables --}}
                    <div class="table-responsive">
                        <table id="productsTable" class="table table-striped table-bordered display nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    {{-- PRIORIDAD ALTA EN MÓVIL: Nombre, Stock, Acciones --}}
                                    <th style="width: 20%">Nombre</th>
                                    <th style="width: 10%">Stock</th>
                                    <th style="width: 15%">Acciones</th> 
                                    
                                    {{-- PRIORIDAD BAJA EN MÓVIL: Se ocultan bajo el botón + --}}
                                    <th style="width: 10%">Código</th>
                                    <th style="width: 15%">Categoría</th>
                                    <th style="width: 10%">Ubicación</th>
                                    <th>Costo/Precio</th>
                                    <th style="width: 10%">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($products as $product)
                                    <tr>
                                        <td><strong>{{ $product->name }}</strong></td>
                                        {{-- Stock --}}
                                        <td data-order="{{ $product->stock ?? 0 }}">
                                            <span class="badge {{ $product->stock <= $product->min_stock ? 'badge-danger' : 'badge-success' }}">
                                                {{ $product->stock }} {{ $product->unit->abbreviation ?? 'unid' }}
                                            </span>
                                        </td>
                                        
                                        {{-- Acciones (Agrupadas para mejor táctil) --}}
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group" aria-label="Acciones de Producto">
                                                
                                                @can('productos_editar')
                                                    <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-default text-primary" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endcan

                                                @can('kardex_ver')
                                                    <a href="{{ route('admin.reports.kardex', $product->id) }}" class="btn btn-default text-info" title="Ver Kardex/Historial">
                                                        <i class="fas fa-history"></i>
                                                    </a>
                                                @endcan
                                                
                                                @can('productos_eliminar')
                                                    <form action="{{ route('admin.products.destroy', $product) }}" method="POST" style="display:inline-block;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-default text-danger" title="Eliminar" onclick="return confirm('¿Seguro de eliminar este producto? Se recomienda solo si no tiene movimientos históricos.')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </td>
                                        
                                        {{-- Columnas Ocultas en Móvil --}}
                                        <td><strong>{{ $product->code ?? 'N/A' }}</strong></td>
                                        <td><span class="badge badge-secondary">{{ $product->category->name ?? 'N/A' }}</span></td>
                                        <td>{{ $product->location->name ?? 'N/A' }}</td>
                                        <td>
                                            <small class="d-block text-muted">C: ${{ number_format($product->cost, 2) }}</small>
                                            P: **${{ number_format($product->price, 2) }}**
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $product->is_active ? 'info' : 'secondary' }}">
                                                {{ $product->is_active ? 'Activo' : 'Inactivo' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No se encontraron productos registrados.</td>
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
            const productsTable = $('#productsTable').DataTable({
                "responsive": true, 
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "order": [[ 0, "asc" ]], // Ordenar por la columna Nombre (índice 0) ascendente por defecto
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
                },
                "columnDefs": [
                    { "orderable": false, "targets": [2, 6] }, // Acciones (índice 2) y Costo/Precio (índice 6)
                    
                    // 🔑 PRIORIDADES MÓVIL: Solo tres columnas son críticas al inicio
                    { "responsivePriority": 1, "targets": 0 }, // Nombre
                    { "responsivePriority": 2, "targets": 2 }, // Acciones
                    { "responsivePriority": 3, "targets": 1 }, // Stock
                    
                    // 🔑 Bajas prioridades: Se ocultan primero
                    { "responsivePriority": 100, "targets": [3, 4, 5, 6, 7] } // Código, Categoría, Ubicación, Costo/Precio, Estado
                ]
            });
            
            // Forzar Redibujo para solucionar problemas de responsive en AdminLTE
            setTimeout(function() {
                productsTable.columns.adjust().responsive.recalc();
            }, 500);
        });
    </script>
@endsection