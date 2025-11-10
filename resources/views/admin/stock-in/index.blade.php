@extends('adminlte::page')

@section('title', 'Entradas de Stock')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1>➕ Registro de Entradas de Stock</h1>
        @can('entradas_crear')
            <a href="{{ route('admin.stock-in.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Nueva Entrada
            </a>
        @endcan
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            @include('admin.partials.session-messages')

            <div class="card">
                <div class="card-body p-0">
                    <table class="table table-striped table-hover table-sm">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Costo Unitario</th>
                                <th>Proveedor</th>
                                <th>Documento</th>
                                <th>Registrado por</th>
                                <th width="80px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($stockIns as $in)
                                <tr>
                                    <td>**{{ $in->entry_date->format('Y-m-d') }}**</td>
                                    <td>{{ $in->product->code }} - {{ $in->product->name }}</td>
                                    <td><span class="badge badge-success">{{ $in->quantity }} {{ $in->product->unit->abbreviation }}</span></td>
                                    <td>${{ number_format($in->unit_cost, 2) }}</td>
                                    <td>{{ $in->supplier->name ?? 'Ajuste / N/A' }}</td>
                                    <td>
                                        {{ $in->document_type }}
                                        <br>
                                        <small class="text-muted">{{ $in->document_number }}</small>
                                    </td>
                                    <td>{{ $in->user->name }}</td>
                                    <td>
                                        <form action="{{ route('admin.stock-in.destroy', $in) }}" method="POST">
                                            @csrf
                                            @method('DELETE')

                                            @can('entradas_eliminar')
                                                <button type="submit" class="btn btn-xs btn-default text-danger" title="Eliminar y Corregir Stock" onclick="return confirm('⚠️ ¡ADVERTENCIA! ¿Estás seguro de eliminar esta entrada? Esto DEVOLVERÁ el stock del producto a su estado anterior.')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endcan
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">No se encontraron movimientos de entrada registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    {{ $stockIns->links() }}
                </div>
            </div>
        </div>
    </div>
@stop
