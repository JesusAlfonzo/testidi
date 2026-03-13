@extends('adminlte::page')

@section('title', 'Crear Solicitud de Cotización')

@section('plugins.Select2', true)
@section('plugins.Sweetalert2', true)

@section('content_header')
    <h1><i class="fas fa-file-invoice"></i> Nueva Solicitud de Cotización</h1>
@stop

@section('content')
    @php
        $categories = \App\Models\Category::orderBy('name')->get();
        $units = \App\Models\Unit::orderBy('name')->get();
        $locations = \App\Models\Location::orderBy('name')->get();
        $brands = \App\Models\Brand::orderBy('name')->get();
    @endphp
    

    <!-- Modal para crear Kit rápido -->
    <div class="modal fade" id="kitModal" tabindex="-1" role="dialog" aria-labelledby="kitModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);">
                    <h5 class="modal-title text-white" id="kitModalLabel"><i class="fas fa-boxes"></i> Crear Nuevo Kit</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="kitForm">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="kit_code">Código/SKU (*)</label>
                                    <input type="text" name="code" id="kit_code" class="form-control" required>
                                    <small class="text-danger" id="kitCodeError" style="display:none;">El código ya existe</small>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="kit_name">Nombre (*)</label>
                                    <input type="text" name="name" id="kit_name" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="kit_category_id">Categoría (*)</label>
                                    <select name="category_id" id="kit_category_id" class="form-control select2" required>
                                        <option value="">Seleccione...</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="kit_unit_id">Unidad (*)</label>
                                    <select name="unit_id" id="kit_unit_id" class="form-control select2" required>
                                        <option value="">Seleccione...</option>
                                        @foreach($units as $unit)
                                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="kit_brand_id">Marca</label>
                                    <select name="brand_id" id="kit_brand_id" class="form-control select2">
                                        <option value="">Seleccione...</option>
                                        @foreach($brands as $brand)
                                            <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="kit_location_id">Ubicación (*)</label>
                                    <select name="location_id" id="kit_location_id" class="form-control select2" required>
                                        <option value="">Seleccione...</option>
                                        @foreach($locations as $location)
                                            <option value="{{ $location->id }}">{{ $location->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="kit_cost">Costo</label>
                                    <input type="number" step="0.01" name="cost" id="kit_cost" class="form-control" value="0" min="0">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning" id="saveKitBtn">
                            <i class="fas fa-save"></i> Guardar Kit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.rfq.store') }}" method="POST" id="rfqForm">
        @csrf

        <!-- Sección: Información General -->
        <div class="row">
            <div class="col-12">
                <div class="card" style="border-left: 4px solid #6c757d;">
                    <div class="card-header" style="background: linear-gradient(135deg, #6c757d 0%, #8a939d 100%);">
                        <h3 class="card-title text-white">
                            <i class="fas fa-info-circle"></i> Información General
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-md-3">
                                <div class="form-group mb-2">
                                    <label for="code" class="mb-1">Código RFQ</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-secondary text-white"><i class="fas fa-hashtag"></i></span>
                                        </div>
                                        <input type="text" name="code" class="form-control" value="{{ $code }}" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-9">
                                <div class="form-group mb-2">
                                    <label for="title" class="mb-1">Título / Asunto (*)</label>
                                    <input type="text" name="title" class="form-control form-control-sm @error('title') is-invalid @enderror" value="{{ old('title') }}" required>
                                    @error('title')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group mb-2">
                                    <label for="date_required" class="mb-1">Fecha Límite de Respuesta</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-info text-white"><i class="fas fa-calendar"></i></span>
                                        </div>
                                        <input type="date" name="date_required" class="form-control form-control-sm @error('date_required') is-invalid @enderror" value="{{ old('date_required') }}">
                                    </div>
                                    @error('date_required')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group mb-2">
                                    <label for="delivery_deadline" class="mb-1">Fecha Límite de Entrega</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-info text-white"><i class="fas fa-truck"></i></span>
                                        </div>
                                        <input type="date" name="delivery_deadline" class="form-control form-control-sm @error('delivery_deadline') is-invalid @enderror" value="{{ old('delivery_deadline') }}">
                                    </div>
                                    @error('delivery_deadline')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group mb-2">
                                    <label for="description" class="mb-1">Descripción / Instrucciones</label>
                                    <textarea name="description" id="description" rows="2" class="form-control form-control-sm @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                                    @error('description')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección: Productos -->
        <div class="row">
            <div class="col-12">
                <div class="card" style="border-left: 4px solid #ef4444;">
                    <div class="card-header" style="background: linear-gradient(135deg, #ef4444 0%, #f87171 100%);">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="card-title text-white">
                                <i class="fas fa-boxes"></i> Productos a Cotizar
                            </h3>
                            <div>
                                <button type="button" id="addKitItem" class="btn btn-sm btn-warning text-dark mr-2">
                                    <i class="fas fa-plus"></i> Agregar Kit
                                </button>
                                <button type="button" id="addItem" class="btn btn-sm btn-light text-danger">
                                    <i class="fas fa-plus"></i> Agregar Producto
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped mb-0" id="itemsTable">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="width: 45%">Producto</th>
                                        <th style="width: 20%">Cantidad</th>
                                        <th style="width: 25%">Notas</th>
                                        <th style="width: 10%"></th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody">
                                    <tr>
                                        <td>
                                            <select name="items[0][product_id]" class="form-control select2-product form-control-sm" required>
                                                <option value="">Seleccione...</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}" data-unit="{{ $product->unit->abbreviation ?? 'und' }}">
                                                        {{ $product->name }} ({{ $product->code ?? 'S/C' }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="items[0][quantity]" class="form-control form-control-sm" min="1" value="1" required>
                                        </td>
                                        <td>
                                            <input type="text" name="items[0][notes]" class="form-control form-control-sm" placeholder="Opcional">
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-danger remove-item" style="display:none;">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección: Notas -->
        <div class="row">
            <div class="col-12">
                <div class="card" style="border-left: 4px solid #9ca3af;">
                    <div class="card-header" style="background: linear-gradient(135deg, #9ca3af 0%, #d1d5db 100%);">
                        <h3 class="card-title text-white">
                            <i class="fas fa-sticky-note"></i> Notas Internas
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-0">
                            <textarea name="notes" id="notes" rows="2" class="form-control form-control-sm @error('notes') is-invalid @enderror" placeholder="Notas visibles solo internamente">{{ old('notes') }}</textarea>
                            @error('notes')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body d-flex justify-content-between">
                        <a href="{{ route('admin.rfq.index') }}" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="button" class="btn btn-primary btn-lg" id="saveRfqBtn">
                            <i class="fas fa-save"></i> Guardar RFQ
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Modal para crear producto -->
    <div class="modal fade" id="productModal" tabindex="-1" role="dialog" aria-labelledby="productModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);">
                    <h5 class="modal-title text-white" id="productModalLabel"><i class="fas fa-box"></i> Crear Nuevo Producto</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="productForm">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="product_code">Código/SKU (*)</label>
                                    <input type="text" name="code" id="product_code" class="form-control" required>
                                    <small class="text-danger" id="codeError" style="display:none;">El código ya existe</small>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="product_name">Nombre (*)</label>
                                    <input type="text" name="name" id="product_name" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="product_category_id">Categoría (*)</label>
                                    <select name="category_id" id="product_category_id" class="form-control select2" required>
                                        <option value="">Seleccione...</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="product_unit_id">Unidad (*)</label>
                                    <select name="unit_id" id="product_unit_id" class="form-control select2" required>
                                        <option value="">Seleccione...</option>
                                        @foreach($units as $unit)
                                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="product_location_id">Ubicación (*)</label>
                                    <select name="location_id" id="product_location_id" class="form-control select2" required>
                                        <option value="">Seleccione...</option>
                                        @foreach($locations as $location)
                                            <option value="{{ $location->id }}">{{ $location->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="product_brand_id">Marca</label>
                                    <select name="brand_id" id="product_brand_id" class="form-control select2">
                                        <option value="">Seleccione...</option>
                                        @foreach($brands as $brand)
                                            <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="product_description">Descripción</label>
                                    <textarea name="description" id="product_description" rows="2" class="form-control"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="product_cost">Costo</label>
                                    <input type="number" step="0.01" name="cost" id="product_cost" class="form-control" value="0" min="0">
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="product_price">Precio</label>
                                    <input type="number" step="0.01" name="price" id="product_price" class="form-control" value="0" min="0">
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="product_min_stock">Stock Mínimo</label>
                                    <input type="number" name="min_stock" id="product_min_stock" class="form-control" value="0" min="0">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="saveProductBtn">
                            <i class="fas fa-save"></i> Guardar Producto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@stop

@section('css')
    <style>
        .select2-container--default .select2-selection--single {
            height: 34px;
            padding-top: 4px;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 34px;
        }
    </style>
@endsection

@section('js')
    <script>
        let itemIndex = 1;
        let currentProductSelect = null;

        function addProductOption(product) {
            const newOption = new Option(
                `${product.name} (${product.code})`, 
                product.id, 
                false, 
                false
            );
            newOption.dataset.unit = product.unit ? product.unit.abbreviation : 'und';
            return newOption;
        }

        function refreshProductSelects() {
            $.get('{{ route("admin.products.search") }}', function(products) {
                $('.select2-product').each(function() {
                    const currentVal = $(this).val();
                    $(this).empty();
                    $(this).append('<option value="">Seleccione...</option>');
                    products.forEach(function(product) {
                        $(this).append(addProductOption(product));
                    }, $(this));
                    $(this).val(currentVal).trigger('change');
                });
            });
        }

        function initSelect2() {
            $('.select2-product').select2({
                theme: 'bootstrap4',
                width: '100%',
                allowClear: true
            });
        }

        function updateRemoveButtons() {
            const rows = $('#itemsBody tr').length;
            $('#itemsBody .remove-item').toggle(rows > 1);
        }

        
        $("#addKitItem").click(function() {
            $("#kitModal").modal("show");
        });

        $(document).on("click", ".create-kit-btn", function(e) {
            e.preventDefault();
            currentProductSelect = $(this).closest(".input-group").find(".select2-product");
            $("#kitModal").modal("show");
        });

        $("#kitForm").on("submit", function(e) {
            e.preventDefault();
            const btn = $("#saveKitBtn");
            btn.prop("disabled", true).html("<i class=\"fas fa-spinner fa-spin\"></i> Guardando...");
            $.ajax({
                url: '/admin/products/quick-store-kit',
                method: "POST",
                data: $(this).serialize(),
                headers: {"X-CSRF-TOKEN": $("meta[name=\"csrf-token\"]").attr("content")},
                success: function(response) {
                    $("#kitModal").modal("hide");
                    $("#kitForm")[0].reset();
                    $("#kitModal .select2").val("").trigger("change");
                    const row = "<tr><td><select name=\"items["+itemIndex+"][product_id]\" class=\"form-control select2-product form-control-sm\" required><option value=\""+response.product.id+"\" selected>"+response.product.name+" ("+response.product.code+") [KIT]</option></select></td><td><input type=\"number\" name=\"items["+itemIndex+"][quantity]\" class=\"form-control form-control-sm\" min=\"1\" value=\"1\" required></td><td><input type=\"text\" name=\"items["+itemIndex+"][notes]\" class=\"form-control form-control-sm\" placeholder=\"Opcional\"></td><td class=\"text-center\"><button type=\"button\" class=\"btn btn-sm btn-danger remove-item\"><i class=\"fas fa-times\"></i></button></td></tr>";
                    $("#itemsBody").append(row);
                    itemIndex++;
                    initSelect2();
                    updateRemoveButtons();
                    refreshProductSelects();
                    Swal.fire({icon: "success", title: "¡Éxito!", text: response.message, timer: 2000, showConfirmButton: false});
                },
                error: function(xhr) { if(xhr.status===422){if(xhr.responseJSON.errors.code){$("#kitCodeError").show();}} else {Swal.fire({icon:"error",title:"Error",text:"Hubo un error al guardar el kit"});} },
                complete: function() { btn.prop("disabled",false).html("<i class=\"fas fa-save\"></i> Guardar Kit"); }
            });
        });

        $("#kitModal").on("hidden.bs.modal", function() { $("#kitForm")[0].reset(); $("#kitCodeError").hide(); });

        $("#kit_code").on("blur", function() { const c=$(this).val(); if(c){$.get("/admin/products/search",{search:c},function(p){const e=p.some(x=>x.code.toLowerCase()===c.toLowerCase());$("#kitCodeError").toggle(e);}); }});
                function attachProductButtonEvents() {
            $('.create-product-btn').off('click').on('click', function(e) {
                e.preventDefault();
                currentProductSelect = $(this).closest('.input-group').find('.select2-product');
                $('#productModal').modal('show');
            });
        }

        $('#addItem').click(function() {
            const row = `
                <tr>
                    <td>
                        <select name="items[${itemIndex}][product_id]" class="form-control select2-product form-control-sm" required>
                            <option value="">Seleccione...</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" data-unit="{{ $product->unit->abbreviation ?? 'und' }}">
                                    {{ $product->name }} ({{ $product->code ?? 'S/C' }})
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="number" name="items[${itemIndex}][quantity]" class="form-control form-control-sm" min="1" value="1" required>
                    </td>
                    <td>
                        <input type="text" name="items[${itemIndex}][notes]" class="form-control form-control-sm" placeholder="Opcional">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger remove-item">
                            <i class="fas fa-times"></i>
                        </button>
                    </td>
                </tr>
            `;
            $('#itemsBody').append(row);
            itemIndex++;
            initSelect2();
            updateRemoveButtons();
        });

        $(document).on('click', '.remove-item', function() {
            $(this).closest('tr').remove();
            updateRemoveButtons();
        });

        $('#productForm').on('submit', function(e) {
            e.preventDefault();
            
            const btn = $('#saveProductBtn');
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
            
            $.ajax({
                url: '{{ route("admin.products.quick-store") }}',
                method: 'POST',
                data: $(this).serialize(),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#productModal').modal('hide');
                    $('#productForm')[0].reset();
                    $('#productModal .select2').val('').trigger('change');
                    
                    if (currentProductSelect) {
                        const newOption = new Option(
                            `${response.product.name} (${response.product.code})`, 
                            response.product.id, 
                            true, 
                            true
                        );
                        currentProductSelect.append(newOption).trigger('change');
                    }
                    
                    refreshProductSelects();
                    
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        if (errors.code) {
                            $('#codeError').show();
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Hubo un error al guardar el producto'
                        });
                    }
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar Producto');
                }
            });
        });

        $('#productModal').on('hidden.bs.modal', function() {
            $('#productForm')[0].reset();
            $('#codeError').hide();
            $('#productModal .select2').val('').trigger('change');
        });

        $('#product_code').on('blur', function() {
            const code = $(this).val();
            if (code) {
                $.get('{{ route("admin.products.search") }}', { search: code }, function(products) {
                    const exists = products.some(p => p.code.toLowerCase() === code.toLowerCase());
                    $('#codeError').toggle(exists);
                });
            }
        });

        $(document).ready(function() {
            initSelect2();
            updateRemoveButtons();
            attachProductButtonEvents();

            // Modal de confirmación para guardar RFQ
            document.getElementById('saveRfqBtn').addEventListener('click', function() {
                confirmAction({
                    title: 'Crear Solicitud de Cotización',
                    message: '¿Está seguro de crear esta Solicitud de Cotización?',
                    alert: 'Verifique que todos los productos y cantidades sean correctos.',
                    confirmBtnClass: 'btn-primary',
                    onConfirm: function() {
                        document.getElementById('rfqForm').submit();
                    }
                });
            });
        });
    </script>
    @include('admin.partials.confirm-action')
@endsection
