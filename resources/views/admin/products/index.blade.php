@extends('adminlte::page')

@section('title', 'Inventario | Productos')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1>📦 Listado de Productos</h1>
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

            <div class="card">
                <div class="card-body p-0">
                    <table class="table table-striped table-hover table-sm">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Categoría</th>
                                <th>Ubicación</th>
                                <th>Stock</th>
                                <th>Costo/Precio</th>
                                <th>Estado</th>
                                <th width="150px">Acciones</th> {{-- Aumentamos el ancho para el nuevo botón --}}
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($products as $product)
                                <tr>
                                    <td><strong>{{ $product->code }}</strong></td>
                                    <td>{{ $product->name }}</td>
                                    <td><span class="badge badge-secondary">{{ $product->category->name }}</span></td>
                                    <td>{{ $product->location->name }}</td>
                                    <td>
                                        <span class="badge {{ $product->stock <= $product->min_stock ? 'badge-danger' : 'badge-success' }}">
                                            {{ $product->stock }} {{ $product->unit->abbreviation }}
                                        </span>
                                    </td>
                                    <td>
                                        C: ${{ number_format($product->cost, 2) }}
                                        <br>
                                        P: ${{ number_format($product->price, 2) }}
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $product->is_active ? 'info' : 'secondary' }}">
                                            {{ $product->is_active ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td>
                                        <form action="{{ route('admin.products.destroy', $product) }}" method="POST" style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')

                                            @can('productos_editar')
                                                <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-xs btn-default text-primary" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan

                                            {{-- NUEVO: Botón de Kardex --}}
                                            @can('kardex_ver')
                                                <a href="{{ route('admin.reports.kardex', $product->id) }}" class="btn btn-xs btn-default text-info" title="Ver Kardex/Historial">
                                                    <i class="fas fa-history"></i>
                                                </a>
                                            @endcan

                                            @can('productos_eliminar')
                                                <button type="submit" class="btn btn-xs btn-default text-danger" title="Eliminar" onclick="return confirm('¿Seguro de eliminar este producto? Se recomienda solo si no tiene movimientos históricos.')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endcan
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">No se encontraron productos registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </div>
@stop
