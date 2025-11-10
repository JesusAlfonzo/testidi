{{--
    Este parcial representa una fila de producto en la solicitud.
    Variables esperadas:
    - $index: El índice numérico para los campos (0, 1, 2, ...).
    - $products: La colección de productos disponibles (ID => Nombre).
    - $item (opcional): Datos antiguos (old input) si la validación falla.
--}}
<div class="row item-row mb-2 border-bottom pb-2 pt-2" data-index="{{ $index }}">

    {{-- Columna para la selección del Producto --}}
    <div class="col-md-7">
        <div class="form-group">
            <label for="items_{{ $index }}_product_id">Producto</label>
            <select name="items[{{ $index }}][product_id]"
                    id="items_{{ $index }}_product_id"
                    class="form-control form-control-sm select2-product"
                    required>
                <option value="">Seleccione un producto</option>
                {{-- Determinar el valor seleccionado si existe --}}
                @php
                    // Usamos el null coalescing operator (??) para manejar el caso donde $item es nulo
                    $selectedProductId = $item['product_id'] ?? null;
                @endphp
                @foreach ($products as $id => $name)
                    <option value="{{ $id }}" {{ $selectedProductId == $id ? 'selected' : '' }}>
                        {{ $name }}
                    </option>
                @endforeach
            </select>
            @error("items.{$index}.product_id")
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
    </div>

    {{-- Columna para la Cantidad Solicitada --}}
    <div class="col-md-3">
        <div class="form-group">
            <label for="items_{{ $index }}_quantity">Cantidad</label>
            <input type="number"
                   name="items[{{ $index }}][quantity]"
                   id="items_{{ $index }}_quantity"
                   class="form-control form-control-sm"
                   value="{{ $item['quantity'] ?? 1 }}"
                   min="1"
                   required>
            @error("items.{$index}.quantity")
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
    </div>

    {{-- Columna para el botón de Eliminación --}}
    <div class="col-md-2 d-flex align-items-end">
        <div class="form-group">
            <button type="button" class="btn btn-danger btn-sm remove-item-btn">
                <i class="fas fa-trash"></i> Eliminar
            </button>
        </div>
    </div>
</div>
