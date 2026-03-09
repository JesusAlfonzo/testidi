<div class="btn-group btn-group-sm" role="group">
    @can('productos_editar')
        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-default text-primary" title="Editar">
            <i class="fas fa-edit"></i>
        </a>
    @endcan
    @can('kardex_ver')
        <a href="{{ route('admin.reports.kardex', $product->id) }}" class="btn btn-default text-info" title="Ver Kardex">
            <i class="fas fa-history"></i>
        </a>
    @endcan
    @can('productos_eliminar')
        <button type="button" class="btn btn-default text-danger" title="Eliminar" onclick="confirmDelete('{{ route('admin.products.destroy', $product) }}', '{{ $product->name }}')">
            <i class="fas fa-trash"></i>
        </button>
    @endcan
</div>
