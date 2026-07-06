@extends('adminlte::page')

@section('title', 'Nueva Solicitud de Salida')

@section('plugins.Select2', true)
@section('plugins.Sweetalert2', true)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="text-dark font-weight-bold" style="font-size: 1.75rem;">
                <i class="fas fa-file-medical text-primary mr-2"></i> Nueva Solicitud de Salida
            </h1>
            <p class="text-muted mb-0">Genere un nuevo despacho o egreso de mercancía del almacén.</p>
        </div>
        <a href="{{ route('admin.requests.index') }}" class="btn btn-secondary px-3 py-2" style="border-radius: 8px;">
            <i class="fas fa-arrow-left mr-1"></i> Volver al Listado
        </a>
    </div>
@stop

@section('content')
    @php
        $showStock = auth()->user()->hasAnyRole(['Superadmin', 'Administrador', 'Supervisor', 'Logistica', 'Encargado Inventario']);
    @endphp

    @include('admin.partials.session-messages')

    <form action="{{ route('admin.requests.store') }}" method="POST" id="requestForm">
        @csrf
        
        <div class="row">
            {{-- COLUMNA IZQUIERDA (70%) - DATOS ORIGEN E ÍTEMS --}}
            <div class="col-lg-8">
                {{-- Datos de Origen --}}
                <div class="card card-custom p-3 bg-white mb-3" style="border-radius: 12px; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <h5 class="font-weight-bold text-dark mb-3"><i class="fas fa-info-circle text-info mr-2"></i> Datos de Origen / Destino</h5>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-group mb-0">
                                <label for="destination_area" class="text-xs font-weight-bold text-secondary text-uppercase mb-1 d-block">Departamento Solicitante <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light" style="border-top-left-radius: 8px; border-bottom-left-radius: 8px;"><i class="fas fa-building text-muted"></i></span>
                                    </div>
                                    @php
                                        $areas = ['Administración', 'Citometria', 'Compras', 'Coordinacion de Laboratorio', 'Dirección', 'Informatica', 'Inmunodiagnostico', 'Inmunogenetica', 'Investigación', 'Lavado', 'Mantenimiento', 'Mensajeria', 'Recepción', 'Retrovirus', 'Seguridad', 'Toma de Muestra'];
                                        sort($areas);
                                    @endphp
                                    <select name="destination_area" id="destination_area" class="form-control @error('destination_area') is-invalid @enderror" style="border-top-right-radius: 8px; border-bottom-right-radius: 8px;" required>
                                        <option value="">Seleccione Departamento...</option>
                                        @foreach($areas as $area)
                                            <option value="{{ $area }}" {{ old('destination_area') == $area ? 'selected' : '' }}>{{ $area }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('destination_area')<span class="text-danger text-xs mt-1 d-block">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="form-group mb-0">
                                <label for="reference" class="text-xs font-weight-bold text-secondary text-uppercase mb-1 d-block">Referencia / Proyecto <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light" style="border-top-left-radius: 8px; border-bottom-left-radius: 8px;"><i class="fas fa-tag text-muted"></i></span>
                                    </div>
                                    <input type="text" name="reference" id="reference" class="form-control @error('reference') is-invalid @enderror" 
                                           value="{{ old('reference') }}" placeholder="Ej: Proyecto Vacunas, Uso Interno" style="border-top-right-radius: 8px; border-bottom-right-radius: 8px;" required>
                                </div>
                                @error('reference')<span class="text-danger text-xs mt-1 d-block">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Filtro de Categorías --}}
                @include('admin.partials.category-filter')

                {{-- Tabla de Ítems Solicitados --}}
                <div class="card card-custom p-3 bg-white mb-3" style="border-radius: 12px; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="font-weight-bold text-dark mb-0"><i class="fas fa-boxes text-danger mr-2"></i> Ítems a Despachar</h5>
                        <button type="button" class="btn btn-sm btn-outline-danger font-weight-bold" id="add-item-btn" style="border-radius: 8px;">
                            <i class="fas fa-plus mr-1"></i> Agregar Ítem
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover" id="itemsTable">
                            <thead>
                                <tr class="bg-light text-secondary text-xs font-weight-bold text-uppercase">
                                    @if($showStock)
                                        <th style="width: 55%; border: none;">Ítem Solicitado</th>
                                        <th style="width: 20%; border: none;">Stock Disponible</th>
                                    @else
                                        <th style="width: 75%; border: none;">Ítem Solicitado</th>
                                    @endif
                                    <th style="width: 20%; border: none;">Cant. Solicitada</th>
                                    <th style="width: 5%; border: none;"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                @php $itemIndex = 0; @endphp
                                @if(old('items'))
                                    @foreach(old('items') as $index => $item)
                                        @include('admin.requests.partials.modern_item_row', ['index' => $index, 'item' => $item])
                                        @php $itemIndex = $index + 1; @endphp
                                    @endforeach
                                @else
                                    @include('admin.requests.partials.modern_item_row', ['index' => 0, 'item' => []])
                                    @php $itemIndex = 1; @endphp
                                @endif
                            </tbody>
                        </table>
                    </div>
                    @error('items')
                        <div class="text-danger text-sm mt-2">
                            <i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            {{-- COLUMNA DERECHA (30%) - TARJETA DE CONTROL Y JUSTIFICACIÓN --}}
            <div class="col-lg-4">
                {{-- Panel de Control --}}
                <div class="card card-custom p-3 bg-white mb-3" style="border-radius: 12px; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <h5 class="font-weight-bold text-dark mb-3"><i class="fas fa-cog text-primary mr-2"></i> Control de Despacho</h5>
                    
                    <div class="form-group mb-3">
                        <label for="priority" class="text-xs font-weight-bold text-secondary text-uppercase mb-1 d-block">Prioridad <span class="text-danger">*</span></label>
                        <select name="priority" id="priority" class="form-control" style="border-radius: 8px;" required>
                            <option value="baja" {{ old('priority') == 'baja' ? 'selected' : '' }}>Baja</option>
                            <option value="media" {{ old('priority', 'media') == 'media' ? 'selected' : '' }}>Media</option>
                            <option value="alta" {{ old('priority') == 'alta' ? 'selected' : '' }}>Alta</option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="justification" class="text-xs font-weight-bold text-secondary text-uppercase mb-1 d-block">Justificación / Razón <span class="text-danger">*</span></label>
                        <textarea name="justification" id="justification" class="form-control @error('justification') is-invalid @enderror" 
                                  rows="4" style="border-radius: 8px;" placeholder="Explique brevemente para qué se requieren estos insumos..." required>{{ old('justification') }}</textarea>
                        @error('justification')<span class="text-danger text-xs mt-1 d-block">{{ $message }}</span>@enderror
                    </div>

                    {{-- Solicitante info --}}
                    <div class="bg-light p-3 rounded mb-3" style="border-radius: 8px;">
                        <span class="text-xs text-secondary font-weight-bold text-uppercase d-block mb-2">Resumen de Solicitante</span>
                        <table class="table table-sm mb-0 text-sm" style="background: transparent;">
                            <tr style="border: none;">
                                <td style="border: none; padding: 2px 0;"><i class="fas fa-user text-muted mr-1"></i> Usuario:</td>
                                <td class="font-weight-bold text-right" style="border: none; padding: 2px 0;">{{ auth()->user()->name }}</td>
                            </tr>
                            <tr>
                                <td style="border: none; padding: 2px 0;"><i class="fas fa-calendar text-muted mr-1"></i> Fecha:</td>
                                <td class="font-weight-bold text-right" style="border: none; padding: 2px 0;">{{ now()->format('d/m/Y') }}</td>
                            </tr>
                        </table>
                    </div>

                    <div class="alert alert-info py-2 px-3 text-xs mb-3" style="border-radius: 8px;">
                        <i class="fas fa-info-circle mr-1"></i> Esta solicitud quedará con estado <strong>Pendiente</strong> y requerirá aprobación para descontar el stock físico.
                    </div>

                    {{-- Botones de Acción --}}
                    <div class="d-flex flex-column">
                        <button type="submit" class="btn btn-primary font-weight-bold py-2 mb-2" style="border-radius: 8px;">
                            <i class="fas fa-save mr-1"></i> Crear Solicitud
                        </button>
                        <a href="{{ route('admin.requests.index') }}" class="btn btn-outline-secondary font-weight-bold py-2" style="border-radius: 8px;">
                            Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
@stop

@section('js')
<script>
    let itemIndex = {{ $itemIndex }};

    // ─── ESTADO DEL FILTRO DE CATEGORÍAS ────────────────────────────────────────
    // allProducts: fuente de verdad inmutable con todos los productos
    const allProducts = [
        @foreach($products as $p)
        {
            id: {{ $p->id }},
            text: '{{ addslashes($p->name) }} ({{ $p->code ?? 'N/A' }})',
            stock: {{ $p->stock ?? 0 }},
            categoryId: {{ $p->category_id ?? 'null' }}
        },
        @endforeach
    ];

    // selectedCategoryId: categoría actualmente seleccionada en el filtro
    let selectedCategoryId = null;

    // filteredProducts(): devuelve el array filtrado según la categoría activa
    function filteredProducts() {
        if (!selectedCategoryId) return [];
        return allProducts.filter(p => p.categoryId == selectedCategoryId);
    }

    // buildProductOptionsHtml(): genera el HTML de las <option> filtradas
    function buildProductOptionsHtml() {
        const products = filteredProducts();
        if (products.length === 0) {
            return '<option value="">— Sin productos en esta categoría —</option>';
        }
        let html = '<option value="">Seleccione un producto...</option>';
        products.forEach(p => {
            html += `<option value="${p.id}" data-stock="${p.stock}">${p.text}</option>`;
        });
        return html;
    }

    // applyFilterToExistingDropdowns(): actualiza los <select> visibles SIN tocar las filas ya guardadas
    function applyFilterToExistingDropdowns() {
        const enabled = !!selectedCategoryId;
        const hint = document.getElementById('categoryFilterHint');

        $('#itemsBody tr').each(function() {
            const select = $(this).find('.select2-product');
            if (select.length === 0) return;

            const currentVal = select.val(); // preservar selección si ya eligió
            const newOptions = buildProductOptionsHtml();

            // Destruir select2, reemplazar opciones, reinicializar
            if (select.data('select2')) select.select2('destroy');
            select.html(newOptions);

            // Si el producto actualmente seleccionado no está en el nuevo filtro,
            // NO lo forzamos a blank — lo mantenemos como opción huérfana visible
            if (currentVal && !filteredProducts().find(p => p.id == currentVal)) {
                const orphan = allProducts.find(p => p.id == currentVal);
                if (orphan) {
                    select.prepend(`<option value="${orphan.id}" data-stock="${orphan.stock}" selected>[${orphan.text}]</option>`);
                    select.val(orphan.id);
                }
            } else {
                select.val(currentVal || '');
            }

            select.prop('disabled', !enabled).select2({
                theme: 'bootstrap4',
                width: '100%',
                placeholder: enabled ? 'Seleccione un producto...' : 'Seleccione una categoría primero'
            });
        });

        if (hint) {
            hint.innerHTML = enabled
                ? `<i class="fas fa-check-circle text-success"></i> Mostrando productos de la categoría seleccionada.`
                : `<i class="fas fa-info-circle"></i> Seleccione una categoría para habilitar el selector de productos.`;
        }
    }
    // ─────────────────────────────────────────────────────────────────────────────

    const showStock = {{ $showStock ? 'true' : 'false' }};

    function addItemRow() {
        const tbody = document.getElementById('itemsBody');
        const tr = document.createElement('tr');
        const enabled = !!selectedCategoryId;
        const options = buildProductOptionsHtml();
        
        let stockCellHtml = '';
        if (showStock) {
            stockCellHtml = `
                <td style="vertical-align: middle;">
                    <span class="stock-display text-muted">-</span>
                    <input type="hidden" name="items[${itemIndex}][stock_available]" class="stock-input" value="">
                </td>
            `;
        }

        tr.innerHTML = `
            <input type="hidden" name="items[${itemIndex}][item_type]" value="product">
            <td style="vertical-align: middle;">
                <select name="items[${itemIndex}][product_id]" class="form-control form-control-sm select2-product" onchange="updateStock(this)" ${enabled ? '' : 'disabled'}>
                    ${options}
                </select>
            </td>
            ${stockCellHtml}
            <td style="vertical-align: middle;">
                <input type="number" name="items[${itemIndex}][quantity]" class="form-control form-control-sm" value="1" min="1" required style="border-radius: 6px;">
            </td>
            <td style="vertical-align: middle;" class="text-center">
                <button type="button" class="btn btn-default text-danger btn-sm" onclick="removeItem(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        
        tbody.appendChild(tr);
        
        const $select = $(tr).find('.select2-product');
        $select.select2({
            theme: 'bootstrap4',
            width: '100%',
            placeholder: enabled ? 'Seleccione un producto...' : 'Seleccione una categoría primero'
        });
        $select.on('select2:select change', function() { updateStock(this); });
        
        itemIndex++;
    }

    function updateStock(selectElement) {
        if (!showStock) return;
        
        let row = $(selectElement).closest('tr');
        if (!row.length) {
            row = $(selectElement).parent().closest('tr');
        }
        
        const stockDisplay = row.find('.stock-display');
        const stockInput = row.find('.stock-input');
        
        const select = $(selectElement);
        const selectedValue = select.val();
        
        if (selectedValue) {
            const option = select.find('option[value="' + selectedValue + '"]');
            const stock = option.data('stock');
            
            if (stock !== undefined && stock !== null) {
                stockDisplay.text(stock);
                stockDisplay.removeClass('text-danger text-muted font-weight-bold');
                stockDisplay.addClass(stock <= 5 ? 'text-danger font-weight-bold' : 'text-muted');
                stockInput.val(stock);
            } else {
                stockDisplay.text('-');
                stockDisplay.removeClass('text-danger font-weight-bold');
                stockDisplay.addClass('text-muted');
                stockInput.val('');
            }
        } else {
            stockDisplay.text('-');
            stockDisplay.removeClass('text-danger font-weight-bold');
            stockDisplay.addClass('text-muted');
            stockInput.val('');
        }
    }

    function removeItem(button) {
        const tbody = document.getElementById('itemsBody');
        if (tbody.rows.length > 1) {
            button.closest('tr').remove();
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'Debe mantener al menos un ítem en la solicitud.'
            });
        }
    }

    $(document).ready(function() {
        // Inicializar selector de categorías — estado inicial: deshabilitado
        applyFilterToExistingDropdowns();

        // Al cambiar la categoría: solo actualiza el dropdown, nunca toca las filas ya guardadas
        $('#categoryFilter').on('change', function() {
            selectedCategoryId = $(this).val() || null;
            applyFilterToExistingDropdowns();
        });

        $(document).on('select2:select change', '.select2-product', function() {
            updateStock(this);
        });

        $('#add-item-btn').click(function() {
            if (!selectedCategoryId) {
                Swal.fire({
                    icon: 'info',
                    title: 'Seleccione una categoría',
                    text: 'Elija primero una categoría en el filtro para poder agregar productos.'
                });
                return;
            }
            addItemRow();
        });

        $('#requestForm').on('submit', function(e) {
            const itemCount = $('#itemsBody tr').length;
            if (itemCount === 0) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Error de Validación',
                    text: 'Debe agregar al menos un ítem a la solicitud.'
                });
                return false;
            }
        });
    });
</script>
@stop
