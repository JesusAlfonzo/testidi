@extends('adminlte::page')

@section('title', 'Maestros | Categorías')

@section('content_header')
    <h1 class="m-0 text-dark">📚 Categorías de Inventario</h1>
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
                    <h3 class="card-title">Listado de Categorías</h3>
                    <div class="card-tools">
                        @can('categorias_crear')
                            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Crear Nueva Categoría
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
                                <th>Descripción</th>
                                <th>Registrado Por</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $category)
                                <tr>
                                    <td>{{ $category->id }}</td>
                                    <td><strong>{{ $category->name }}</strong></td>
                                    <td>{{ $category->description ?? 'N/A' }}</td>
                                    <td>{{ $category->user->name ?? 'Sistema' }}</td>
                                    <td>
                                        <div class="btn-group">
                                            @can('categorias_editar')
                                                <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-xs btn-warning" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan

                                            @can('categorias_eliminar')
                                                <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('⚠️ ADVERTENCIA: ¿Está seguro de eliminar esta categoría? Esto podría afectar a productos asociados.')" title="Eliminar">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No hay categorías registradas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="card-footer">
                    {{ $categories->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
@stop
