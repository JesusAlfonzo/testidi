<script id="item-row-template" type="text/template">
    <div class="row item-row border border-light rounded p-3 mb-3 bg-light align-items-end">
        
        {{-- Selector de Tipo (Producto / Kit) --}}
        <div class="col-md-2">
            <div class="form-group">
                <label>Tipo de Ítem</label>
                <select name="items[__INDEX__][item_type]" 
                        id="type_select___INDEX__" 
                        data-index="__INDEX__" 
                        class="form-control type-selector" 
                        required>
                    <option value="product" selected>Producto</option>
                    <option value="kit">Kit</option>
                </select>
            </div>
        </div>

        {{-- Selector de Producto (Contenedor con ID único) --}}
        <div class="col-md-6" id="container_product___INDEX__">
            <div class="form-group">
                <label>Producto</label>
                <select name="items[__INDEX__][product_id]" 
                        id="input_product___INDEX__" 
                        class="form-control select2-product">
                    <option value="">Seleccione un producto...</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}">
                            {{ $product->name }} (Stock: {{ $product->stock }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        
        {{-- Selector de Kit (Contenedor con ID único - Oculto por defecto) --}}
        <div class="col-md-6" id="container_kit___INDEX__" style="display:none;">
            <div class="form-group">
                <label>Kit / Paquete</label>
                <select name="items[__INDEX__][kit_id]" 
                        id="input_kit___INDEX__" 
                        class="form-control select2-kit">
                    <option value="">Seleccione un kit...</option>
                    @foreach ($kits as $kit)
                        <option value="{{ $kit->id }}">{{ $kit->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Cantidad Solicitada --}}
        <div class="col-md-3">
            <div class="form-group">
                <label>Cantidad</label>
                <input type="number" name="items[__INDEX__][quantity]" class="form-control" value="1" min="1" required>
            </div>
        </div>
        
        {{-- Botón de Eliminar --}}
        <div class="col-md-1">
            <div class="form-group">
                <button type="button" class="btn btn-danger btn-sm remove-item-btn" title="Eliminar ítem">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>
</script>