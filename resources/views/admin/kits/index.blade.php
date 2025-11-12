@extends('adminlte::page')

@section('title', 'Inventario | Kits de Inventario')

{{-- Plugins necesarios: DataTables, Plugins y Responsive --}}
@section('plugins.Datatables', true) 
@section('plugins.DatatablesPlugins', true) 
@section('plugins.Responsive', true) 

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1 class="m-0 text-dark"><i class="fas fa-boxes"></i> Kits de Inventario</h1>
        <a href="{{ route('admin.kits.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Crear Nuevo Kit
        </a>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            @include('admin.partials.session-messages')

            {{-- Cambiamos a la estructura de Card normal que hemos usado, quitando la x-adminlte-card para uniformidad --}}
            <div class="card card-outline card-info">
                <div class="card-body p-0">
                    {{-- 🔑 table-responsive y clases de Datatables --}}
                    <div class="table-responsive">
                        <table id="kitsTable" class="table table-striped table-bordered display nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    {{-- Prioridad Alta en Móvil --}}
                                    <th style="width: 10%">ID</th>
                                    <th style="width: 40%">Nombre</th>
                                    <th style="width: 15%">Acciones</th> 
                                    
                                    {{-- Prioridad Baja en Móvil --}}
                                    <th style="width: 20%">Precio Unitario</th>
                                    <th style="width: 15%">Activo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($kits as $kit)
                                    <tr>
                                        <td>{{ $kit->id }}</td>
                                        <td><strong>{{ $kit->name }}</strong></td>
                                        
                                        {{-- Acciones (Agrupadas para mejor táctil) --}}
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group" aria-label="Acciones de Kit">
                                                <a href="{{ route('admin.kits.edit', $kit) }}" class="btn btn-default text-primary" title="Editar"><i class="fas fa-edit"></i></a>
                                                
                                                <form action="{{ route('admin.kits.destroy', $kit) }}" method="POST" style="display:inline;" onsubmit="return confirm('¿Está seguro de eliminar este Kit?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-default text-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                        
                                        {{-- Columnas Ocultas en Móvil --}}
                                        <td data-order="{{ $kit->unit_price }}">
                                            ${{ number_format($kit->unit_price, 2) }}
                                        </td>
                                        <td>
                                            @if($kit->is_active)
                                                <span class="badge badge-success">Sí</span>
                                            @else
                                                <span class="badge badge-danger">No</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div> {{-- Cierre de table-responsive --}}
                </div>
                {{-- Eliminamos la paginación de Laravel, ahora manejada por DataTables --}}
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
            const kitsTable = $('#kitsTable').DataTable({
                "responsive": true, 
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "order": [[ 1, "asc" ]], // Ordenar por la columna Nombre (índice 1) ascendente por defecto
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
                },
                "columnDefs": [
                    { "orderable": false, "targets": [2] }, // Acciones (índice 2)
                    
                    // 🔑 PRIORIDADES MÓVIL: Definimos qué se queda visible.
                    { "responsivePriority": 1, "targets": 1 }, // Nombre
                    { "responsivePriority": 2, "targets": 2 }, // Acciones
                    { "responsivePriority": 3, "targets": 0 }, // ID
                    
                    // 🔑 Bajas prioridades: Se ocultan primero
                    { "responsivePriority": 100, "targets": [3, 4] } // Precio Unitario, Activo
                ]
            });
            
            // Forzar Redibujo para solucionar problemas de responsive en AdminLTE
            setTimeout(function() {
                kitsTable.columns.adjust().responsive.recalc();
            }, 500);
        });
    </script>
@endsection