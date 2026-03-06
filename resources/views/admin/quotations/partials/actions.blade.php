<div class="btn-group btn-group-sm" role="group">
    <a href="{{ route('admin.quotations.show', $quotation) }}" class="btn btn-default text-info" title="Ver">
        <i class="fas fa-eye"></i>
    </a>
    <a href="{{ route('admin.quotations.pdf', $quotation) }}" class="btn btn-default text-secondary" title="PDF" target="_blank">
        <i class="fas fa-file-pdf"></i>
    </a>
    @if($quotation->isEditable())
        @can('cotizaciones_editar')
            <a href="{{ route('admin.quotations.edit', $quotation) }}" class="btn btn-default text-primary" title="Editar">
                <i class="fas fa-edit"></i>
            </a>
        @endcan
        @can('cotizaciones_eliminar')
            <form action="{{ route('admin.quotations.destroy', $quotation) }}" method="POST" style="display:inline-block;">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-default text-danger" title="Eliminar" onclick="return confirm('¿Eliminar esta cotización?')">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        @endcan
    @endif
</div>
