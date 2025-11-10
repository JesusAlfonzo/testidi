@extends('adminlte::page')

@section('title', 'Maestros | Unidades de Medida')

@section('content_header')
    <h1 class="m-0 text-dark">📏 Unidades de Medida</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            {{-- Mensajes de feedback --}}
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Listado de Unidades</h3>
                    <div class="card-tools">
                        @can('unidades_crear')
                            <a href="{{ route('admin.units.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Crear Nueva Unidad
                            </a>
                        @endcan
                    </div>
                </div>

                <div class="card-body p-0">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Abreviatura</th>
                                <th>Registrado Por</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($units as $unit)
                                <tr>
                                    <td>{{ $unit->id }}</td>
                                    <td><strong>{{ $unit->name }}</strong></td>
                                    <td><span class="badge badge-info">{{ $unit->abbreviation }}</span></td>
                                    <td>{{ $unit->user->name ?? 'Sistema' }}</td>
                                    <td>
                                        <div class="btn-group">
                                            @can('unidades_editar')
                                                <a href="{{ route('admin.units.edit', $unit) }}" class="btn btn-xs btn-warning" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan

                                            @can('unidades_eliminar')
                                                <form action="{{ route('admin.units.destroy', $unit) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('⚠️ ADVERTENCIA: ¿Está seguro de eliminar esta Unidad? Esto podría afectar a productos asociados.')" title="Eliminar">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No hay unidades de medida registradas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="card-footer">
                    {{ $units->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
@stop
