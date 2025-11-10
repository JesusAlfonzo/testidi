<script id="item-row-template" type="text/template">
    <div class="row item-row border border-light rounded p-3 mb-3 bg-light align-items-end">
        
        {{-- Selector de Tipo (Producto / Kit) --}}
        <div class="col-md-2">
            <div class="form-group">
                <label>Tipo de Ítem</label>
                <select name="items[__INDEX__][item_type]" id="type_select___INDEX__" data-index="__INDEX__" class="form-control" required>
                    <option value="product">Producto</option>
                    <option value="kit">Kit</option>
                </select>
            </div>
        </div>

        {{-- Selector de Producto --}}
        <div class="col-md-6" id="product_selector___INDEX__">
            <div class="form-group">
                <label>Producto</label>
                <select name="items[__INDEX__][product_id]" id="item_select___INDEX__" class="form-control select2">
                    <option value="">Seleccione un producto...</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}">
                            {{ $product->name }} (Stock: {{ $product->stock }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        
        {{-- Selector de Kit (Hidden by default) --}}
        <div class="col-md-6" id="kit_selector___INDEX__" style="display:none;">
            <div class="form-group">
                <label>Kit / Paquete</label>
                {{-- Nota: El campo product_id debe ir NULL si seleccionamos un kit, 
                   y el kit_id debe ir NULL si seleccionamos un producto. --}}
                <select name="items[__INDEX__][kit_id]" class="form-control select2">
                    <option value="">Seleccione un kit...</option>
                    @foreach ($kits as $kit)
                        <option value="{{ $kit->id }}">{{ $kit->name }}</option>
                    @endforeach
                </select>
                <input type="hidden" name="items[__INDEX__][product_id]" value="">
            </div>
        </div>

        {{-- Cantidad Solicitada --}}
        <div class="col-md-3">
            <x-adminlte-input name="items[__INDEX__][quantity]" type="number" label="Cantidad" value="1" min="1" required/>
        </div>
        
        {{-- Botón de Eliminar --}}
        <div class="col-md-1">
            <button type="button" class="btn btn-danger btn-sm remove-item-btn" title="Eliminar ítem">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
</script>