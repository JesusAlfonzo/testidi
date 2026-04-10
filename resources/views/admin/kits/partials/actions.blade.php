@can('kits_editar')
    <a href="{{ route('admin.kits.edit', $kit) }}" class="btn btn-default text-primary" title="Editar"><i class="fas fa-edit"></i></a>
@endcan

@can('kits_eliminar')
    <button type="button" class="btn btn-default text-danger" title="Eliminar" onclick="confirmDelete('{{ route('admin.kits.destroy', $kit) }}', '{{ $kit->name }}')">
        <i class="fas fa-trash"></i>
    </button>
@endcan
