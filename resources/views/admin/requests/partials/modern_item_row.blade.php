@php
    $type = $item['item_type'] ?? 'product';
    $prodId = $item['product_id'] ?? null;
    $kitId = $item['kit_id'] ?? null;
    $qty = $item['quantity'] ?? 1;
@endphp

<tr>
    <td>
        <select name="items[{{ $index }}][item_type]" class="form-control form-control-sm type-selector" onchange="toggleType(this)">
            <option value="product" {{ $type == 'product' ? 'selected' : '' }}>Producto</option>
            <option value="kit" {{ $type == 'kit' ? 'selected' : '' }}>Kit</option>
        </select>
    </td>
    <td>
        <select name="items[{{ $index }}][product_id]" class="form-control form-control-sm select2-product" 
                style="{{ $type == 'product' ? '' : 'display:none;' }}">
            <option value="">Seleccione...</option>
            @foreach($products as $p)
                <option value="{{ $p->id }}" data-stock="{{ $p->stock }}" {{ $prodId == $p->id ? 'selected' : '' }}>
                    {{ $p->name }} ({{ $p->code }})
                </option>
            @endforeach
        </select>
        <select name="items[{{ $index }}][kit_id]" class="form-control form-control-sm select2-kit" 
                style="{{ $type == 'kit' ? '' : 'display:none;' }}">
            <option value="">Seleccione...</option>
            @foreach($kits as $k)
                <option value="{{ $k->id }}" {{ $kitId == $k->id ? 'selected' : '' }}>
                    {{ $k->name }}
                </option>
            @endforeach
        </select>
    </td>
    <td>
        @php
            $stockDisplay = '-';
            if ($type == 'product' && $prodId) {
                $product = $products->firstWhere('id', $prodId);
                if ($product) {
                    $stockDisplay = $product->stock;
                }
            }
        @endphp
        <span class="stock-display {{ $stockDisplay != '-' && $stockDisplay <= 5 ? 'text-danger font-weight-bold' : 'text-muted' }}">
            {{ $stockDisplay }}
        </span>
        <input type="hidden" name="items[{{ $index }}][stock_available]" class="stock-input" value="{{ $stockDisplay != '-' ? $stockDisplay : '' }}">
    </td>
    <td>
        <input type="number" name="items[{{ $index }}][quantity]" class="form-control form-control-sm" value="{{ $qty }}" min="1" required>
    </td>
    <td>
        <button type="button" class="btn btn-danger btn-sm" onclick="removeItem(this)">
            <i class="fas fa-trash"></i>
        </button>
    </td>
</tr>
