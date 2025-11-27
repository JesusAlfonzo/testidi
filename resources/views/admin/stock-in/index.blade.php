@extends('adminlte::page')

@section('title', 'Entradas de Stock')

{{-- Plugins necesarios --}}
@section('plugins.Datatables', true) 
@section('plugins.DatatablesPlugins', true) 
@section('plugins.Responsive', true) 
@section('plugins.Select2', true)

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1><i class="fas fa-arrow-alt-circle-up"></i> Entradas de Stock</h1>
        @can('entradas_crear')
            <a href="{{ route('admin.stock-in.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Nueva Entrada
            </a>
        @endcan
    </div>
@stop

{{-- 🔑 AJUSTE CRÍTICO EN EL CSS --}}
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
    
    {{-- 🔎 FILTROS DE BÚSQUEDA --}}
    <div class="card card-outline card-primary collapsed-card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter"></i> Filtros</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.stock-in.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Desde</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Hasta</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Producto</label>
                            <select name="product_id" class="form-control select2">
                                <option value="">Todos</option>
                                @foreach($products as $id => $name)
                                    <option value="{{ $id }}" {{ request('product_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-group w-100">
                            <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-search"></i> Filtrar</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- TABLA DE RESULTADOS --}}
    <div class="row">
        <div class="col-12">
            @include('admin.partials.session-messages')

            <div class="card card-outline card-success">
                <div class="card-body p-4">
                    <div class="table-responsive">
                        {{-- 🔑 ID 'stockInTable', clases 'display nowrap' y width 100% --}}
                        <table id="stockInTable" class="table table-striped table-bordered display nowrap" style="width:100%">
                            <thead>
                                <tr>
                                    {{-- Prioridad Alta en Móvil --}}
                                    <th style="width: 15%">Fecha</th>
                                    <th style="width: 25%">Producto</th>
                                    <th style="width: 10%">Cantidad</th>
                                    <th style="width: 10%">Acciones</th>
                                    
                                    {{-- Prioridad Baja en Móvil --}}
                                    <th>Costo Unit.</th>
                                    <th>Proveedor</th>
                                    <th>Documento</th>
                                    <th>Registrado por</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($stockIns as $in)
                                    <tr>
                                        {{-- 1. Fecha --}}
                                        <td data-order="{{ $in->entry_date->timestamp }}">
                                            <strong>{{ $in->entry_date->format('d/m/Y') }}</strong>
                                        </td>
                                        
                                        {{-- 2. Producto --}}
                                        <td>
                                            <strong>{{ $in->product->name }}</strong>
                                            <br><small class="text-muted">{{ $in->product->code }}</small>
                                        </td>
                                        
                                        {{-- 3. Cantidad --}}
                                        <td class="text-center">
                                            <span class="badge badge-success" style="font-size: 0.9rem;">
                                                +{{ $in->quantity }} {{ $in->product->unit->abbreviation ?? '' }}
                                            </span>
                                        </td>
                                        
                                        {{-- 4. Acciones --}}
                                        <td class="text-center">
                                            @can('entradas_eliminar')
                                                <form action="{{ route('admin.stock-in.destroy', $in) }}" method="POST">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-default text-danger btn-sm" title="Eliminar y Revertir Stock" onclick="return confirm('⚠️ ¡ADVERTENCIA! ¿Estás seguro de eliminar esta entrada? Esto RESTARÁ el stock del producto.')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </td>
                                        
                                        {{-- Columnas Ocultas --}}
                                        <td>${{ number_format($in->unit_cost, 2) }}</td>
                                        <td>{{ $in->supplier->name ?? 'Ajuste / N/A' }}</td>
                                        <td>
                                            {{ $in->document_type }} 
                                            @if($in->document_number)
                                                <span class="badge badge-light">{{ $in->document_number }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $in->user->name }}</td>
                                    </tr>
                                @empty
                                    {{-- DataTables manejará el vacío --}}
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Inicializar Select2
            $('.select2').select2({ theme: 'bootstrap4' });

            // Inicializar DataTables
            const stockInTable = $('#stockInTable').DataTable({
                "responsive": true, 
                "paging": true, 
                "lengthChange": true, 
                "searching": true, 
                "ordering": true, 
                "info": true, 
                "autoWidth": false,
                "order": [[ 0, "desc" ]], // Ordenar por Fecha (índice 0) descendente
                
                // 🔑 Traducción Nativa
                "language": {
                    "decimal": "",
                    "emptyTable": "No hay entradas registradas",
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
                    { "orderable": false, "targets": [3] }, // Acciones no ordenables
                    { "type": "date", "targets": 0 },       // Tipo fecha
                    
                    // 🔑 PRIORIDADES PARA MÓVIL
                    { "responsivePriority": 1, "targets": 1 }, // Producto (Lo más importante)
                    { "responsivePriority": 2, "targets": 2 }, // Cantidad
                    { "responsivePriority": 3, "targets": 0 }, // Fecha
                    { "responsivePriority": 4, "targets": 3 }, // Acciones
                    
                    // Ocultar detalles secundarios en móvil
                    { "responsivePriority": 100, "targets": [4, 5, 6, 7] } 
                ]
            });
            
            // Ajuste de renderizado
            setTimeout(function() { 
                stockInTable.columns.adjust().responsive.recalc(); 
            }, 500);
        });
    </script>
@endsection