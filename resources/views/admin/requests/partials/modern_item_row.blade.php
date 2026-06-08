@php
    $prodId = $item['product_id'] ?? null;
    $qty = $item['quantity'] ?? 1;
@endphp

<tr>
    <input type="hidden" name="items[{{ $index }}][item_type]" value="product">
    <td>
        <select name="items[{{ $index }}][product_id]" class="form-control form-control-sm select2-product">
            <option value="">Seleccione...</option>
            @foreach($products as $p)
                <option value="{{ $p->id }}" data-stock="{{ $p->stock }}" {{ $prodId == $p->id ? 'selected' : '' }}>
                    {{ $p->name }} ({{ $p->code ?? 'N/A' }})
                </option>
            @endforeach
        </select>
    </td>
    <td>
        @php
            $stockDisplay = '-';
            if ($prodId) {
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
