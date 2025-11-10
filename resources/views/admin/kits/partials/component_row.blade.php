{{-- Fila para manejar valores antiguos después de un error de validación --}}
<div class="row component-row border border-light rounded p-2 mb-2 bg-light">
    {{-- Producto ID --}}
    <div class="col-md-8">
        <div class="form-group">
            <label for="product_{{ $index }}">Producto</label>
            <select name="components[{{ $index }}][product_id]" class="form-control select2" required>
                <option value="">Seleccione un producto</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}" {{ (isset($oldComponent) && $oldComponent['product_id'] == $product->id) ? 'selected' : '' }}>
                        {{ $product->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Cantidad Requerida --}}
    <div class="col-md-3">
        <x-adminlte-input name="components[{{ $index }}][quantity]" type="number" label="Cant. Requerida" value="{{ $oldComponent['quantity'] ?? 1 }}" min="1" required/>
    </div>
    
    {{-- Botón de Eliminar --}}
    <div class="col-md-1 d-flex align-items-center">
        <button type="button" class="btn btn-danger btn-sm mt-3 remove-component-btn" title="Eliminar componente">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>