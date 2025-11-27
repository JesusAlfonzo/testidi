@php
    // Determinar valores iniciales
    $type = $item['item_type'] ?? 'product';
    $prodId = $item['product_id'] ?? null;
    $kitId = $item['kit_id'] ?? null;
    $qty = $item['quantity'] ?? 1;
@endphp

<div class="row item-row border-bottom pb-3 mb-3" data-index="{{ $index }}">
    {{-- Selector de Tipo --}}
    <div class="col-md-2">
        <div class="form-group mb-1">
            <label class="small">Tipo</label>
            <select name="items[{{ $index }}][item_type]" class="form-control form-control-sm type-selector">
                <option value="product" {{ $type == 'product' ? 'selected' : '' }}>Producto</option>
                <option value="kit" {{ $type == 'kit' ? 'selected' : '' }}>Kit</option>
            </select>
        </div>
    </div>

    {{-- Selector de Producto --}}
    <div class="col-md-6 container-product" style="{{ $type == 'product' ? '' : 'display:none;' }}">
        <div class="form-group mb-1">
            <label class="small">Producto</label>
            <select name="items[{{ $index }}][product_id]" class="form-control form-control-sm select2 select-product">
                <option value="">Seleccione Producto...</option>
                @foreach($products as $p)
                    <option value="{{ $p->id }}" {{ $prodId == $p->id ? 'selected' : '' }}>
                        {{ $p->name }} (Stock: {{ $p->stock }})
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Selector de Kit --}}
    <div class="col-md-6 container-kit" style="{{ $type == 'kit' ? '' : 'display:none;' }}">
        <div class="form-group mb-1">
            <label class="small">Kit</label>
            <select name="items[{{ $index }}][kit_id]" class="form-control form-control-sm select2 select-kit">
                <option value="">Seleccione Kit...</option>
                @foreach($kits as $k)
                    <option value="{{ $k->id }}" {{ $kitId == $k->id ? 'selected' : '' }}>
                        {{ $k->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Cantidad --}}
    <div class="col-md-3">
        <div class="form-group mb-1">
            <label class="small">Cantidad</label>
            <input type="number" name="items[{{ $index }}][quantity]" class="form-control form-control-sm" value="{{ $qty }}" min="1">
        </div>
    </div>

    {{-- Botón Eliminar --}}
    <div class="col-md-1 d-flex align-items-center pt-3">
        <button type="button" class="btn btn-danger btn-xs remove-item-btn" title="Quitar"><i class="fas fa-times"></i></button>
    </div>
</div>