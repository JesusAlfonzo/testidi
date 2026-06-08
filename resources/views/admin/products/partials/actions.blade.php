<div class="btn-group btn-group-sm" role="group">
    <a href="{{ route('admin.products.show', $product->id) }}" class="btn btn-default text-info" title="Ver Detalle">
        <i class="fas fa-eye"></i>
    </a>
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
        <button class="btn btn-sm btn-danger btn-delete-product" data-id="{{ $product->id }}" data-url="{{ route('admin.products.destroy', $product->id) }}">
            <i class="fas fa-trash"></i>
        </button>
    @endcan
</div>
