<div class="btn-group btn-group-sm" role="group">
    @can('proveedores_ver')
        <a href="{{ route('admin.suppliers.show', $supplier) }}" class="btn btn-default text-info" title="Ver Detalle">
            <i class="fas fa-eye"></i>
        </a>
    @endcan

    @can('proveedores_editar')
        <a href="{{ route('admin.suppliers.edit', $supplier) }}" class="btn btn-default text-primary" title="Editar">
            <i class="fas fa-edit"></i>
        </a>
    @endcan

    @can('proveedores_eliminar')
        <button type="button" 
                class="btn btn-default text-danger btn-delete-master" 
                data-id="{{ $supplier->id }}" 
                data-name="{{ $supplier->name }}" 
                data-url="{{ route('admin.suppliers.destroy', $supplier) }}" 
                title="Eliminar">
            <i class="fas fa-trash"></i>
        </button>
    @endcan
</div>
