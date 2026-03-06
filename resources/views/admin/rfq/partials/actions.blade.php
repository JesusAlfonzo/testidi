<div class="btn-group btn-group-sm" role="group">
    <a href="{{ route('admin.rfq.show', $rfq) }}" class="btn btn-default text-info" title="Ver">
        <i class="fas fa-eye"></i>
    </a>
    <a href="{{ route('admin.rfq.pdf', $rfq) }}" class="btn btn-default text-secondary" title="PDF" target="_blank">
        <i class="fas fa-file-pdf"></i>
    </a>
    @if($rfq->isEditable())
        @can('rfq_editar')
            <a href="{{ route('admin.rfq.edit', $rfq) }}" class="btn btn-default text-primary" title="Editar">
                <i class="fas fa-edit"></i>
            </a>
        @endcan
        @can('rfq_eliminar')
            <form action="{{ route('admin.rfq.destroy', $rfq) }}" method="POST" style="display:inline-block;">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-default text-danger" title="Eliminar" onclick="return confirm('¿Eliminar esta RFQ?')">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        @endcan
    @endif
</div>
