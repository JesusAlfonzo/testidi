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
        <form action="{{ route('admin.products.destroy', $product) }}" method="POST" style="display:inline-block;">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-default text-danger" title="Eliminar" onclick="return confirm('¿Seguro de eliminar este producto? Se recomienda solo si no tiene movimientos históricos.')">
                <i class="fas fa-trash"></i>
            </button>
        </form>
    @endcan
</div>
