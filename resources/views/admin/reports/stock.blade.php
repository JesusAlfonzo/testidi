@extends('adminlte::page')

@section('title', 'Reporte de Stock Actual')

@section('content_header')
    <h1>Reporte de Stock Actual</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Listado de Productos y Stock</h3>
            <div class="card-tools">
                <a href="#" class="btn btn-tool btn-sm">
                    <i class="fas fa-download"></i> Exportar a Excel (Funcionalidad futura)
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-valign-middle">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Producto</th>
                        <th>Stock Actual</th>
                        <th>Unidad</th>
                        <th>Stock Mínimo</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $product)
                        @php
                            $isLow = $product->stock < $product->minimum_stock;
                        @endphp
                        <tr>
                            <td>{{ $product->code }}</td>
                            <td>{{ $product->name }}</td>
                            <td>
                                <h4>
                                    <span class="badge badge-{{ $isLow ? 'danger' : 'success' }}">
                                        {{ $product->stock }}
                                    </span>
                                </h4>
                            </td>
                            <td>{{ $product->unit->abbreviation ?? 'N/A' }}</td>
                            <td>{{ $product->minimum_stock }}</td>
                            <td>
                                @if ($isLow)
                                    <span class="badge badge-danger"><i class="fas fa-exclamation-triangle"></i> Bajo Stock</span>
                                @else
                                    <span class="badge badge-secondary">Óptimo</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix">
            {{ $products->links() }}
        </div>
    </div>
@stop
