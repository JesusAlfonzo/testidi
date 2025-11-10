@extends('adminlte::page')

@section('title', 'Proveedores')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1>🚚 Proveedores</h1>
        @can('proveedores_crear')
            <a href="{{ route('admin.suppliers.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Nuevo Proveedor
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
                    <table class="table table-bordered table-hover table-sm">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>ID Fiscal / RUC</th>
                                <th>Contacto / Teléfono</th>
                                <th>Email</th>
                                <th width="150px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($suppliers as $supplier)
                                <tr>
                                    <td>{{ $supplier->id }}</td>
                                    <td><strong>{{ $supplier->name }}</strong></td>
                                    <td>{{ $supplier->tax_id ?? 'N/A' }}</td>
                                    <td>
                                        {{ $supplier->contact_person }}
                                        <br>
                                        <small class="text-muted">{{ $supplier->phone }}</small>
                                    </td>
                                    <td>{{ $supplier->email ?? 'N/A' }}</td>
                                    <td>
                                        <form action="{{ route('admin.suppliers.destroy', $supplier) }}" method="POST" style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')

                                            @can('proveedores_editar')
                                                <a href="{{ route('admin.suppliers.edit', $supplier) }}" class="btn btn-xs btn-default text-primary" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan

                                            @can('proveedores_eliminar')
                                                <button type="submit" class="btn btn-xs btn-default text-danger" title="Eliminar" onclick="return confirm('¿Estás seguro de que deseas eliminar este proveedor?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endcan
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">No se encontraron proveedores registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    {{ $suppliers->links() }}
                </div>
            </div>
        </div>
    </div>
@stop
