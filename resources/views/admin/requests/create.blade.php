@extends('adminlte::page')

@section('title', 'Nueva Solicitud de Salida')

@section('plugins.Select2', true)
@section('plugins.Sweetalert2', true)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-file-medical"></i> Nueva Solicitud de Salida</h1>
        <a href="{{ route('admin.requests.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
@stop

@section('content')
    @include('admin.partials.session-messages')

    <form action="{{ route('admin.requests.store') }}" method="POST" id="requestForm">
        @csrf
        
        <div class="row">
            {{-- Información Principal --}}
            <div class="col-md-12">
                <div class="card" style="border-left: 4px solid #17a2b8;">
                    <div class="card-header" style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);">
                        <h3 class="card-title text-white">
                            <i class="fas fa-info-circle"></i> Datos de la Solicitud
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="reference">Referencia / Proyecto <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-info text-white"><i class="fas fa-tag"></i></span>
                                        </div>
                                        <input type="text" name="reference" class="form-control @error('reference') is-invalid @enderror" 
                                               value="{{ old('reference') }}" placeholder="Ej: Proyecto X, Uso Diario Lab" required>
                                    </div>
                                    @error('reference')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="destination_area">Área de Destino</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-info text-white"><i class="fas fa-map-marker-alt"></i></span>
                                        </div>
                                        <input type="text" name="destination_area" class="form-control @error('destination_area') is-invalid @enderror" 
                                               value="{{ old('destination_area') }}" placeholder="Ej: Laboratorio Central">
                                    </div>
                                    @error('destination_area')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="requested_date">Fecha Requerida</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-info text-white"><i class="fas fa-calendar"></i></span>
                                        </div>
                                        <input type="date" name="requested_date" class="form-control @error('requested_date') is-invalid @enderror" 
                                               value="{{ old('requested_date') }}">
                                    </div>
                                    @error('requested_date')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="justification">Justificación <span class="text-danger">*</span></label>
                                    <textarea name="justification" class="form-control @error('justification') is-invalid @enderror" 
                                              rows="2" required placeholder="Explique brevemente el motivo de la solicitud...">{{ old('justification') }}</textarea>
                                    @error('justification')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Productos / Kits --}}
        <div class="row">
            <div class="col-md-12">
                <div class="card" style="border-left: 4px solid #dc3545;">
                    <div class="card-header" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="card-title text-white">
                                <i class="fas fa-boxes"></i> Ítems Solicitados
                            </h3>
                            <button type="button" class="btn btn-sm btn-light text-danger" id="add-item-btn">
                                <i class="fas fa-plus"></i> Agregar Ítem
                            </button>
                        </div>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-bordered table-striped" id="itemsTable">
                            <thead class="thead-light">
                                <tr>
                                    <th width="5%">Tipo</th>
                                    <th width="40%">Producto / Kit</th>
                                    <th width="15%">Stock Actual</th>
                                    <th width="15%">Cantidad Solicitada</th>
                                    <th width="5%"></th>
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
                                @endif
                            </tbody>
                        </table>
                    </div>
                    @error('items')
                        <div class="card-footer text-danger">
                            <i class="fas fa-exclamation-circle"></i> {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Información Adicional --}}
        <div class="row">
            <div class="col-md-6">
                <div class="card card-secondary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-user"></i> Información del Solicitante</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr>
                                <th width="40%"><i class="fas fa-user"></i> Nombre:</th>
                                <td>{{ auth()->user()->name }}</td>
                            </tr>
                            <tr>
                                <th><i class="fas fa-envelope"></i> Email:</th>
                                <td>{{ auth()->user()->email }}</td>
                            </tr>
                            <tr>
                                <th><i class="fas fa-calendar"></i> Fecha:</th>
                                <td>{{ now()->format('d/m/Y') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card card-info">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-info"></i> Nota</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-0">
                            <i class="fas fa-exclamation-circle"></i> 
                            La solicitud quedará en estado <span class="badge badge-warning">Pendiente</span> 
                            hasta que un administrador la apruebe.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Botones de Acción --}}
        <div class="row mt-3">
            <div class="col-md-12">
                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.requests.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Crear Solicitud
                    </button>
                </div>
            </div>
        </div>
    </form>
@stop

@section('js')
<script>
    let itemIndex = {{ $itemIndex }};

    function addItemRow() {
        const tbody = document.getElementById('itemsBody');
        const tr = document.createElement('tr');
        
        const productOptions = `@foreach($products as $p)<option value="{{ $p->id }}" data-stock="{{ $p->stock }}">{{ $p->name }} ({{ $p->code }})</option>@endforeach`;
        const kitOptions = `@foreach($kits as $k)<option value="{{ $k->id }}">{{ $k->name }}</option>@endforeach`;
        
        tr.innerHTML = `
            <td>
                <select name="items[${itemIndex}][item_type]" class="form-control form-control-sm type-selector" onchange="toggleType(this)">
                    <option value="product">Producto</option>
                    <option value="kit">Kit</option>
                </select>
            </td>
            <td>
                <select name="items[${itemIndex}][product_id]" class="form-control form-control-sm select2-product" onchange="updateStock(this)">
                    <option value="">Seleccione...</option>
                    ${productOptions}
                </select>
                <select name="items[${itemIndex}][kit_id]" class="form-control form-control-sm select2-kit" style="display:none;">
                    <option value="">Seleccione...</option>
                    ${kitOptions}
                </select>
            </td>
            <td>
                <span class="stock-display text-muted">-</span>
                <input type="hidden" name="items[${itemIndex}][stock_available]" class="stock-input" value="">
            </td>
            <td>
                <input type="number" name="items[${itemIndex}][quantity]" class="form-control form-control-sm" value="1" min="1" required>
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm" onclick="removeItem(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        
        tbody.appendChild(tr);
        
        if (typeof $ !== 'undefined' && $.fn.select2) {
            $(tr).find('.select2-product').select2({
                theme: 'bootstrap4',
                width: '100%'
            });
        }
        
        // Agregar eventos para actualizar stock
        $(tr).find('.select2-product').on('select2:select change', function(e) {
            updateStock(this);
        });
        
        itemIndex++;
    }

    function toggleType(select) {
        const row = select.closest('tr');
        const productSelect = row.querySelector('.select2-product');
        const kitSelect = row.querySelector('.select2-kit');
        const stockDisplay = row.querySelector('.stock-display');
        
        if (select.value === 'product') {
            productSelect.style.display = 'block';
            productSelect.style.width = '100%';
            kitSelect.style.display = 'none';
            kitSelect.required = false;
            kitSelect.value = '';
            stockDisplay.textContent = '-';
            
            if (typeof $ !== 'undefined' && $.fn.select2) {
                $(kitSelect).select2('destroy');
                $(productSelect).select2({
                    theme: 'bootstrap4',
                    width: '100%'
                });
            }
        } else {
            productSelect.style.display = 'none';
            kitSelect.style.display = 'block';
            kitSelect.style.width = '100%';
            productSelect.required = false;
            productSelect.value = '';
            stockDisplay.textContent = 'N/A';
            
            if (typeof $ !== 'undefined' && $.fn.select2) {
                $(productSelect).select2('destroy');
                $(kitSelect).select2({
                    theme: 'bootstrap4',
                    width: '100%'
                });
            }
        }
    }

    function updateStock(selectElement) {
        // Encontrar la fila más cercana - Select2 crea un contenedor, entonces primero buscamos ese
        let row = $(selectElement).closest('tr');
        
        // Si no encuentra, usar método nativo
        if (!row.length) {
            row = $(selectElement).parent().closest('tr');
        }
        
        const stockDisplay = row.find('.stock-display');
        const stockInput = row.find('.stock-input');
        
        // Obtener el valor seleccionado directamente del select
        const select = $(selectElement);
        const selectedValue = select.val();
        
        if (selectedValue) {
            // Buscar la opción seleccionada en el DOM del select
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
            alert('Debe mantener al menos un ítem.');
        }
    }

    $(document).ready(function() {
        function initSelect2() {
            $('.select2-product').select2({
                theme: 'bootstrap4',
                width: '100%'
            });
        }

        initSelect2();

        // Evento para select2 - usar select2:select que es el evento específico de select2
        $(document).on('select2:select', '.select2-product', function(e) {
            updateStock(this);
        });
        
        // También escuchar el evento change nativo por si acaso
        $(document).on('change', '.select2-product', function(e) {
            updateStock(this);
        });

        $('#add-item-btn').click(function() {
            addItemRow();
        });

        $('#requestForm').on('submit', function(e) {
            const itemCount = $('#itemsBody tr').length;
            if (itemCount === 0) {
                e.preventDefault();
                alert('Debe agregar al menos un ítem.');
                return false;
            }
        });
    });
</script>
@stop
