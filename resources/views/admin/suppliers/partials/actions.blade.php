<div class="btn-group btn-group-sm" role="group">
    @can('proveedores_editar')
        <a href="{{ route('admin.suppliers.edit', $supplier) }}" class="btn btn-default text-primary" title="Editar">
            <i class="fas fa-edit"></i>
        </a>
    @endcan

    @can('proveedores_eliminar')
        <button type="button" class="btn btn-default text-danger" onclick="confirmDelete('{{ route('admin.suppliers.destroy', $supplier) }}', '{{ $supplier->name }}')" title="Eliminar">
            <i class="fas fa-trash"></i>
        </button>
    @endcan
</div>
