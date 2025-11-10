@extends('adminlte::page')

@section('title', 'Kits de Inventario')

@section('content_header')
    <h1 class="m-0 text-dark">Kits de Inventario</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <x-adminlte-card title="Listado de Kits" icon="fas fa-boxes" class="card-primary">
                <a href="{{ route('admin.kits.create') }}" class="btn btn-success mb-3">Crear Nuevo Kit</a>

                <div class="table-responsive">
                    <table id="kits-table" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Precio Unitario</th>
                                <th>Activo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kits as $kit)
                                <tr>
                                    <td>{{ $kit->id }}</td>
                                    <td>{{ $kit->name }}</td>
                                    <td>${{ number_format($kit->unit_price, 2) }}</td>
                                    <td>
                                        @if($kit->is_active)
                                            <span class="badge badge-success">Sí</span>
                                        @else
                                            <span class="badge badge-danger">No</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.kits.edit', $kit) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fas fa-edit"></i></a>
                                        <form action="{{ route('admin.kits.destroy', $kit) }}" method="POST" style="display:inline;" onsubmit="return confirm('¿Está seguro de eliminar este Kit?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{ $kits->links() }}

            </x-adminlte-card>
        </div>
    </div>
@stop

@push('js')
<script>
    // Inicializa DataTables (asumiendo que ya activaste el plugin en config/adminlte.php)
    $(document).ready(function() {
        $('#kits-table').DataTable({
            "paging": true,
            "lengthChange": true,
            "searching": true, 
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
            }
        });
    });
</script>
@endpush