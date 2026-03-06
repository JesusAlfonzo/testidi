<div class="btn-group btn-group-sm" role="group">
    <a href="{{ route('admin.purchaseOrders.show', $order) }}" class="btn btn-default text-info" title="Ver">
        <i class="fas fa-eye"></i>
    </a>
    <a href="{{ route('admin.purchaseOrders.pdf', $order) }}" class="btn btn-default text-secondary" title="PDF" target="_blank">
        <i class="fas fa-file-pdf"></i>
    </a>
    @if($order->status === 'draft')
        @can('ordenes_compra_editar')
            <a href="{{ route('admin.purchaseOrders.edit', $order) }}" class="btn btn-default text-primary" title="Editar">
                <i class="fas fa-edit"></i>
            </a>
        @endcan
        @can('ordenes_compra_eliminar')
            <form action="{{ route('admin.purchaseOrders.destroy', $order) }}" method="POST" style="display:inline-block;">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-default text-danger" title="Eliminar" onclick="return confirm('¿Eliminar esta orden de compra?')">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        @endcan
    @endif
</div>
