@extends('adminlte::page')

@section('title', 'Marcas')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1>🏷️ Marcas de Insumos</h1>
        @can('marcas_crear')
            <a href="{{ route('admin.brands.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Nueva Marca
            </a>
        @endcan
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            @include('admin.partials.session-messages')

            <div class="card">
                <div class="card-body">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Sitio Web</th>
                                <th>Creado por</th>
                                <th>Fecha Creación</th>
                                <th width="150px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($brands as $brand)
                                <tr>
                                    <td>{{ $brand->id }}</td>
                                    <td><strong>{{ $brand->name }}</strong></td>
                                    <td>
                                        @if($brand->website)
                                            <a href="{{ $brand->website }}" target="_blank" rel="noopener noreferrer">
                                                <i class="fas fa-external-link-alt"></i> Visitar
                                            </a>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>{{ $brand->user->name }}</td>
                                    <td>{{ $brand->created_at->format('Y-m-d') }}</td>
                                    <td>
                                        <form action="{{ route('admin.brands.destroy', $brand) }}" method="POST" style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')

                                            @can('marcas_editar')
                                                <a href="{{ route('admin.brands.edit', $brand) }}" class="btn btn-xs btn-default text-primary" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan

                                            @can('marcas_eliminar')
                                                <button type="submit" class="btn btn-xs btn-default text-danger" title="Eliminar" onclick="return confirm('¿Estás seguro de que deseas eliminar esta marca?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endcan
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">No se encontraron marcas registradas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    {{ $brands->links() }}
                </div>
            </div>
        </div>
    </div>
@stop
