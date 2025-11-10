@extends('adminlte::page')

@section('title', 'Ubicaciones')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1>Ubicaciones de Inventario</h1>
        @can('ubicaciones_crear')
            <a href="{{ route('admin.locations.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Nueva Ubicación
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
                                <th>Detalles</th>
                                <th>Creado por</th>
                                <th>Fecha Creación</th>
                                <th width="150px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($locations as $location)
                                <tr>
                                    <td>{{ $location->id }}</td>
                                    <td>{{ $location->name }}</td>
                                    <td>{{ $location->details ?? 'N/A' }}</td>
                                    <td>{{ $location->user->name }}</td>
                                    <td>{{ $location->created_at->format('Y-m-d') }}</td>
                                    <td>
                                        <form action="{{ route('admin.locations.destroy', $location) }}" method="POST" style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')

                                            @can('ubicaciones_editar')
                                                <a href="{{ route('admin.locations.edit', $location) }}" class="btn btn-xs btn-default text-primary" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan

                                            @can('ubicaciones_eliminar')
                                                <button type="submit" class="btn btn-xs btn-default text-danger" title="Eliminar" onclick="return confirm('¿Estás seguro de que deseas eliminar esta ubicación?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endcan
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">No se encontraron ubicaciones registradas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    {{ $locations->links() }}
                </div>
            </div>
        </div>
    </div>
@stop
