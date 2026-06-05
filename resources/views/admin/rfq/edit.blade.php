@extends('adminlte::page')

@section('title', 'Editar RFQ ' . $rfq->code)

@section('plugins.Select2', true)
@section('plugins.Sweetalert2', true)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-edit text-warning"></i> Editar Solicitud de Cotización (RFQ)</h1>
        <a href="{{ route('admin.rfq.show', $rfq) }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-eye"></i> Ver Detalle
        </a>
    </div>
@stop

@section('content')
    @php
        $categories = \App\Models\Category::orderBy('name')->get();
        $units = \App\Models\Unit::orderBy('name')->get();
        $locations = \App\Models\Location::orderBy('name')->get();
        $brands = \App\Models\Brand::orderBy('name')->get();
    @endphp

    <div class="container-fluid">
        @include('admin.partials.session-messages')

        <form action="{{ route('admin.rfq.update', $rfq) }}" method="POST" id="rfqForm">
            @csrf
            @method('PUT')
            
            <div class="row">
                <!-- Columna Principal (Izquierda - 70%) -->
                <div class="col-lg-9 col-md-12">
                    
                    <!-- Card de Información de la RFQ -->
                    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                        <div class="card-header bg-gradient-white border-bottom py-3">
                            <h3 class="card-title text-dark font-weight-bold mb-0">
                                <i class="fas fa-info-circle text-warning mr-1"></i> Información General
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 col-12">
                                    <div class="form-group mb-3">
                                        <label for="code" class="text-xs text-muted mb-1">Código Correlativo Automático</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light border-right-0"><i class="fas fa-hashtag text-muted"></i></span>
                                            </div>
                                            <input type="text" name="code" class="form-control bg-light border-left-0 font-weight-bold" value="{{ $rfq->code }}" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 col-12">
                                    <div class="form-group mb-3">
                                        <label for="created_date" class="text-xs text-muted mb-1">Fecha de Emisión</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light border-right-0"><i class="fas fa-calendar-alt text-muted"></i></span>
                                            </div>
                                            <input type="text" id="created_date" class="form-control bg-light border-left-0" value="{{ $rfq->created_at->format('d/m/Y') }}" disabled>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 col-12">
                                    <div class="form-group mb-3">
                                        <label for="department" class="text-xs text-muted mb-1">Departamento Solicitante</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light border-right-0"><i class="fas fa-building text-muted"></i></span>
                                            </div>
                                            <input type="text" id="department" class="form-control bg-light border-left-0" value="Compras y Logística" disabled>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group mb-3">
                                        <label for="title" class="text-xs text-muted mb-1">Título / Asunto de la Solicitud <span class="text-danger">*</span></label>
                                        <input type="text" name="title" id="title" class="form-control form-control-sm @error('title') is-invalid @enderror" value="{{ old('title', $rfq->title) }}" placeholder="Ej. Adquisición de repuestos para planta eléctrica" required>
                                        @error('title')<span class="invalid-feedback text-xs">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group mb-0">
                                        <label for="description" class="text-xs text-muted mb-1">Descripción General / Notas</label>
                                        <textarea name="description" id="description" rows="3" class="form-control form-control-sm @error('description') is-invalid @enderror" placeholder="Escriba aquí instrucciones detalladas o términos generales para los proveedores">{{ old('description', $rfq->description) }}</textarea>
                                        @error('description')<span class="invalid-feedback text-xs">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card de Ítems de la Solicitud (Repetidor Dinámico) -->
                    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                        <div class="card-header bg-gradient-white border-bottom py-3 d-flex justify-content-between align-items-center">
                            <h3 class="card-title text-dark font-weight-bold mb-0">
                                <i class="fas fa-list-ul text-warning mr-1"></i> Ítems a Cotizar
                            </h3>
                            <div>
                                <button type="button" class="btn btn-xs btn-primary shadow-sm mr-1" id="quickNewItemBtn">
                                    <i class="fas fa-plus-circle"></i> + Nuevo Ítem
                                </button>
                                <button type="button" class="btn btn-xs btn-success shadow-sm" id="addItemRowBtn">
                                    <i class="fas fa-plus"></i> Añadir Fila
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="itemsTable">
                                    <thead class="bg-light text-muted text-uppercase text-xs font-weight-bold">
                                        <tr>
                                            <th style="width: 50%">Catálogo (Producto / Kit) <span class="text-danger">*</span></th>
                                            <th style="width: 15%">Cantidad <span class="text-danger">*</span></th>
                                            <th style="width: 25%">Especificaciones / Notas</th>
                                            <th style="width: 10%" class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemsBody">
                                        @foreach($rfq->items as $index => $item)
                                            @php
                                                $isSelectedKit = false;
                                                $selectedId = null;
                                                $unitAbbreviation = 'und';

                                                if ($item->item_type === 'kit') {
                                                    $isSelectedKit = true;
                                                    $selectedId = $item->kit_id ?? $item->product_id;
                                                } else {
                                                    // Es product, pero revisemos si el producto es un Kit unificado
                                                    if ($item->product) {
                                                        $isSelectedKit = $item->product->is_kit;
                                                        $selectedId = $item->product->id;
                                                        $unitAbbreviation = $item->product->unit->abbreviation ?? 'und';
                                                    }
                                                }
                                            @endphp
                                            <tr class="item-row" data-index="{{ $index }}">
                                                <td>
                                                    <input type="hidden" name="items[{{ $index }}][item_type]" class="row-item-type" value="{{ $isSelectedKit ? 'kit' : 'product' }}">
                                                    <input type="hidden" name="items[{{ $index }}][product_id]" class="row-product-id" value="{{ !$isSelectedKit ? $selectedId : '' }}">
                                                    <input type="hidden" name="items[{{ $index }}][kit_id]" class="row-kit-id" value="{{ $isSelectedKit ? $selectedId : '' }}">
                                                    
                                                    <select class="form-control form-control-sm select2-item-selector item-selector" required style="width: 100%;">
                                                        <option value="">Seleccione un ítem...</option>
                                                        <optgroup label="Productos Individuales">
                                                            @foreach($products as $product)
                                                                @if(!$product->is_kit)
                                                                    <option value="{{ $product->id }}" data-is-kit="0" data-unit="{{ $product->unit->abbreviation ?? 'und' }}" {{ (!$isSelectedKit && $selectedId == $product->id) ? 'selected' : '' }}>
                                                                        {{ $product->name }} ({{ $product->code ?? 'S/C' }})
                                                                    </option>
                                                                @endif
                                                            @endforeach
                                                        </optgroup>
                                                        <optgroup label="Kits / Compuestos">
                                                            @foreach($products as $product)
                                                                @if($product->is_kit)
                                                                    <option value="{{ $product->id }}" data-is-kit="1" data-unit="{{ $product->unit->abbreviation ?? 'und' }}" {{ ($isSelectedKit && $selectedId == $product->id && $item->kit_id == null) ? 'selected' : '' }}>
                                                                        {{ $product->name }} ({{ $product->code ?? 'S/C' }}) [Kit]
                                                                    </option>
                                                                @endif
                                                            @endforeach
                                                            @foreach($kits as $kit)
                                                                <option value="{{ $kit->id }}" data-is-kit="1" data-unit="und" {{ ($isSelectedKit && $item->kit_id == $kit->id) ? 'selected' : '' }}>
                                                                    {{ $kit->name }} [Kit Legacy]
                                                                </option>
                                                            @endforeach
                                                        </optgroup>
                                                    </select>
                                                </td>
                                                <td>
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" name="items[{{ $index }}][quantity]" class="form-control form-control-sm text-center row-quantity" min="1" value="{{ old("items.$index.quantity", $item->quantity) }}" required>
                                                        <div class="input-group-append">
                                                            <span class="input-group-text row-unit-badge text-muted text-xs bg-light">{{ $unitAbbreviation }}</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <input type="text" name="items[{{ $index }}][notes]" class="form-control form-control-sm" placeholder="Especificaciones adicionales..." value="{{ old("items.$index.notes", $item->notes) }}">
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-outline-danger remove-item" style="{{ $rfq->items->count() <= 1 ? 'display:none;' : '' }}" title="Eliminar fila">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top py-3 d-flex justify-content-between align-items-center">
                            <span class="text-xs text-muted">Asegúrese de agregar al menos un ítem al listado.</span>
                            <span class="text-sm font-weight-bold text-dark">Total de Ítems: <span id="totalItemsCount" class="text-primary font-weight-bold">{{ $rfq->items->count() }}</span></span>
                        </div>
                    </div>

                    <!-- Sección: Notas Internas -->
                    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                        <div class="card-header bg-gradient-white border-bottom py-3">
                            <h3 class="card-title text-dark font-weight-bold mb-0">
                                <i class="fas fa-comment-dots text-warning mr-1"></i> Notas Internas
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-0">
                                <textarea name="notes" id="notes" rows="2" class="form-control form-control-sm @error('notes') is-invalid @enderror" placeholder="Notas internas visibles solo para administradores y supervisores (no se incluyen en el PDF enviado a proveedores)">{{ old('notes', $rfq->notes) }}</textarea>
                                @error('notes')<span class="invalid-feedback text-xs">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Columna Lateral (Derecha - 30%) -->
                <div class="col-lg-3 col-md-12">
                    
                    <!-- Card de Control y Estado -->
                    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; overflow: hidden;">
                        <div class="bg-gradient-warning py-3 px-3">
                            <h3 class="card-title h6 text-dark font-weight-bold mb-0">
                                <i class="fas fa-sliders-h mr-1"></i> Panel de Control
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="text-xs text-muted mb-1 d-block">Estatus RFQ</label>
                                <span class="badge badge-secondary py-2 px-3 d-block font-weight-bold text-sm shadow-sm" style="border-radius: 6px;">
                                    <i class="fas fa-file-alt mr-1"></i> {{ strtoupper($rfq->status === 'draft' ? 'Borrador' : $rfq->status) }}
                                </span>
                            </div>

                            <div class="form-group mb-3">
                                <label for="priority" class="text-xs text-muted mb-1">Prioridad de Solicitud</label>
                                <select name="priority" id="priority" class="form-control form-control-sm select2" style="width: 100%;">
                                    <option value="baja" {{ old('priority', $rfq->priority) == 'baja' ? 'selected' : '' }}>🟢 Baja</option>
                                    <option value="media" {{ old('priority', $rfq->priority) == 'media' ? 'selected' : '' }}>🟡 Media</option>
                                    <option value="alta" {{ old('priority', $rfq->priority) == 'alta' ? 'selected' : '' }}>🔴 Alta</option>
                                </select>
                            </div>

                            <hr class="my-3">

                            <div class="form-group mb-3">
                                <label for="date_required" class="text-xs text-muted mb-1">F. Límite de Respuesta</label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light border-right-0"><i class="fas fa-calendar-times text-danger"></i></span>
                                    </div>
                                    <input type="date" name="date_required" id="date_required" class="form-control border-left-0 form-control-sm @error('date_required') is-invalid @enderror" value="{{ old('date_required', $rfq->date_required?->format('Y-m-d')) }}">
                                </div>
                                @error('date_required')<span class="invalid-feedback text-xs d-block">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group mb-4">
                                <label for="delivery_deadline" class="text-xs text-muted mb-1">F. Límite de Entrega</label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light border-right-0"><i class="fas fa-truck-loading text-primary"></i></span>
                                    </div>
                                    <input type="date" name="delivery_deadline" id="delivery_deadline" class="form-control border-left-0 form-control-sm @error('delivery_deadline') is-invalid @enderror" value="{{ old('delivery_deadline', $rfq->delivery_deadline?->format('Y-m-d')) }}">
                                </div>
                                @error('delivery_deadline')<span class="invalid-feedback text-xs d-block">{{ $message }}</span>@enderror
                            </div>

                            <button type="button" class="btn btn-warning btn-block shadow-sm mb-2 text-dark font-weight-bold" id="saveRfqBtn">
                                <i class="fas fa-save mr-1"></i> Guardar Cambios
                            </button>
                            
                            <a href="{{ route('admin.rfq.pdf', $rfq) }}" class="btn btn-outline-secondary btn-block btn-sm mb-3" target="_blank">
                                <i class="fas fa-file-pdf mr-1"></i> Descargar PDF Genérico
                            </a>

                            <a href="{{ route('admin.rfq.show', $rfq) }}" class="btn btn-outline-danger btn-sm btn-block">
                                <i class="fas fa-times mr-1"></i> Cancelar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Modal de Creación Rápida "En Caliente" -->
    <div class="modal fade" id="quickItemModal" tabindex="-1" role="dialog" aria-labelledby="quickItemModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content shadow border-0" style="border-radius: 12px;">
                <div class="modal-header bg-gradient-warning text-dark py-3">
                    <h5 class="modal-title font-weight-bold" id="quickItemModalLabel">
                        <i class="fas fa-plus-circle mr-1"></i> Crear Nuevo Ítem Rápido
                    </h5>
                    <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="quickItemForm">
                    <div class="modal-body py-4">
                        
                        <!-- Toggle de Tipo: Producto vs Kit -->
                        <div class="row mb-4">
                            <div class="col-12 text-center">
                                <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success d-inline-block">
                                    <input type="checkbox" class="custom-control-input" id="modal_is_kit" name="is_kit" value="1">
                                    <label class="custom-control-label font-weight-bold text-md" for="modal_is_kit">
                                        <i class="fas fa-cubes text-info"></i> Definir como Kit / Compuesto
                                    </label>
                                </div>
                                <div class="text-muted text-xs mt-1">Marque esta casilla si desea asociar múltiples componentes hijos a este ítem.</div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Columna Izquierda del Modal -->
                            <div class="col-md-6 border-right">
                                <h6 class="text-primary font-weight-bold mb-3 border-bottom pb-2">Información Básica</h6>
                                
                                <div class="form-group">
                                    <label for="modal_code" class="text-xs text-muted mb-1">Código/SKU <span class="text-danger">*</span></label>
                                    <input type="text" name="code" id="modal_code" class="form-control form-control-sm" required placeholder="Ej. PROD-0023">
                                    <small class="text-danger text-xs" id="modalCodeError" style="display:none;">El código ya existe.</small>
                                </div>

                                <div class="form-group">
                                    <label for="modal_name" class="text-xs text-muted mb-1">Nombre del Producto <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="modal_name" class="form-control form-control-sm" required placeholder="Ej. Válvula de compresión de 3 pulgadas">
                                </div>

                                <div class="form-group">
                                    <label for="modal_unit_id" class="text-xs text-muted mb-1">Unidad de Medida <span class="text-danger">*</span></label>
                                    <select name="unit_id" id="modal_unit_id" class="form-control select2" required style="width: 100%;">
                                        <option value="">Seleccione...</option>
                                        @foreach($units as $unit)
                                            <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->abbreviation }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="modal_category_id" class="text-xs text-muted mb-1">Categoría <span class="modal-generic-marker text-danger">*</span></label>
                                    <select name="category_id" id="modal_category_id" class="form-control select2" required style="width: 100%;">
                                        <option value="">Seleccione...</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group mb-0">
                                    <label for="modal_location_id" class="text-xs text-muted mb-1">Ubicación de Inventario <span class="modal-generic-marker text-danger">*</span></label>
                                    <select name="location_id" id="modal_location_id" class="form-control select2" required style="width: 100%;">
                                        <option value="">Seleccione...</option>
                                        @foreach($locations as $location)
                                            <option value="{{ $location->id }}">{{ $location->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Columna Derecha del Modal -->
                            <div class="col-md-6">
                                <h6 class="text-primary font-weight-bold mb-3 border-bottom pb-2">Configuraciones y Alertas</h6>
                                
                                <div class="form-group mb-2">
                                    <label for="modal_brand_id" class="text-xs text-muted mb-1">Marca</label>
                                    <select name="brand_id" id="modal_brand_id" class="form-control select2" style="width: 100%;">
                                        <option value="">Seleccione...</option>
                                        @foreach($brands as $brand)
                                            <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group mb-3">
                                    <div class="custom-control custom-switch mt-2">
                                        <input type="hidden" name="is_generic" value="0">
                                        <input type="checkbox" class="custom-control-input" id="modal_is_generic" name="is_generic" value="1">
                                        <label class="custom-control-label text-xs font-weight-bold" for="modal_is_generic">
                                            <i class="fas fa-cubes text-info mr-1"></i> Producto Genérico
                                        </label>
                                    </div>
                                    <small class="form-text text-muted text-xs">Marcar para productos genéricos donde marca, categoría y ubicación son opcionales.</small>
                                </div>

                                <!-- Sección Dinámica 1: Detalles de Producto Individual (Se oculta para kits) -->
                                <div id="productDetailsSection">
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label for="modal_cost" class="text-xs text-muted mb-1">Costo ($)</label>
                                                <input type="number" step="0.01" name="cost" id="modal_cost" class="form-control form-control-sm" value="0.00" min="0">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label for="modal_price" class="text-xs text-muted mb-1">Precio Venta ($)</label>
                                                <input type="number" step="0.01" name="price" id="modal_price" class="form-control form-control-sm" value="0.00" min="0">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="modal_min_stock" class="text-xs text-muted mb-1">Stock Mínimo Alerta</label>
                                        <input type="number" name="min_stock" id="modal_min_stock" class="form-control form-control-sm" value="0" min="0">
                                    </div>
                                    <div class="form-group">
                                        <label for="modal_description" class="text-xs text-muted mb-1">Descripción</label>
                                        <textarea name="description" id="modal_description" rows="2" class="form-control form-control-sm" placeholder="Opcional"></textarea>
                                    </div>
                                </div>

                                <!-- Sección Dinámica 2: Componentes del Kit (Se muestra solo para kits) -->
                                <div id="kitDetailsSection" style="display: none;">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="text-xs text-muted mb-0 font-weight-bold text-uppercase"><i class="fas fa-boxes text-warning mr-1"></i> Componentes del Kit</label>
                                        <button type="button" class="btn btn-xs btn-outline-warning" id="addModalComponentBtn">
                                            <i class="fas fa-plus"></i> Componente
                                        </button>
                                    </div>
                                    <div style="max-height: 200px; overflow-y: auto;" class="border rounded p-2 bg-light mb-2">
                                        <table class="table table-sm table-borderless mb-0 align-middle" id="modalComponentsTable">
                                            <thead>
                                                <tr class="text-xs text-muted border-bottom">
                                                    <th>Item</th>
                                                    <th style="width: 90px;">Cant.</th>
                                                    <th style="width: 35px;"></th>
                                                </tr>
                                            </thead>
                                            <tbody id="modalComponentsBody">
                                                <!-- Rows injected by JS -->
                                            </tbody>
                                        </table>
                                        <div id="noComponentsPlaceholder" class="text-center text-muted text-xs py-3">
                                            No hay componentes añadidos.
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="modal_kit_cost" class="text-xs text-muted mb-1">Costo Estimado del Kit ($)</label>
                                        <input type="number" step="0.01" name="cost" id="modal_kit_cost" class="form-control form-control-sm" value="0.00" min="0">
                                        <small class="text-muted text-xs">Costo de adquisición de este compuesto.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-sm btn-warning text-dark font-weight-bold" id="saveQuickItemBtn">
                            <i class="fas fa-save"></i> Guardar Ítem
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Fuente de Datos para Autocompletar Componentes (Sólo productos simples) -->
    <div style="display:none;" id="modal_simple_products_source">
        <option value="">Seleccione...</option>
        @foreach($products as $product)
            @if(!$product->is_kit)
                <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->code ?? 'S/C' }})</option>
            @endif
        @endforeach
    </div>
@stop

@section('css')
    <style>
        .select2-container--default .select2-selection--single {
            height: 38px;
            padding-top: 5px;
            border-color: #ced4da;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }
        .bg-gradient-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%) !important;
        }
        .bg-gradient-white {
            background: linear-gradient(180deg, #ffffff 0%, #f9fafb 100%) !important;
        }
        .item-row select.is-invalid + .select2-container {
            border: 1px solid #dc3545 !important;
            border-radius: 4px;
        }
        .text-xs {
            font-size: 0.75rem !important;
        }
        .text-md {
            font-size: 1rem !important;
        }
        .custom-switch .custom-control-label::before {
            height: 1.5rem;
            width: 2.75rem;
            border-radius: 1rem;
        }
        .custom-switch .custom-control-label::after {
            width: calc(1.5rem - 4px);
            height: calc(1.5rem - 4px);
            border-radius: 1rem;
        }
        .custom-switch .custom-control-input:checked ~ .custom-control-label::after {
            transform: translateX(1.25rem);
        }
    </style>
@endsection

@section('js')
    <script>
        let itemIndex = {{ $rfq->items->count() }};
        let activeRowForModal = null;

        // Inicializar select2 en toda la página
        function initSelect2(container = document) {
            $(container).find('.select2').each(function() {
                if (!$(this).data('select2')) {
                    $(this).select2({
                        theme: 'bootstrap4',
                        width: '100%',
                        allowClear: true
                    });
                }
            });

            $(container).find('.select2-item-selector').each(function() {
                if (!$(this).data('select2')) {
                    $(this).select2({
                        theme: 'bootstrap4',
                        width: '100%',
                        allowClear: true,
                        placeholder: 'Seleccione un producto o kit...'
                    }).on('change', function() {
                        const row = $(this).closest('tr');
                        const selectedOpt = $(this).find('option:selected');
                        const isKit = selectedOpt.data('is-kit') == 1;
                        const itemId = $(this).val();
                        const unit = selectedOpt.data('unit') || 'und';

                        // Actualizar indicador de unidad
                        row.find('.row-unit-badge').text(unit);

                        // Configurar inputs del form dinámico
                        const index = row.data('index');
                        const typeInput = row.find('.row-item-type');
                        const productInput = row.find('.row-product-id');
                        const kitInput = row.find('.row-kit-id');

                        if (itemId === "") {
                            productInput.val('');
                            kitInput.val('');
                            return;
                        }

                        if (isKit) {
                            typeInput.val('kit');
                            kitInput.val(itemId).attr('name', `items[${index}][kit_id]`);
                            productInput.val('').removeAttr('name');
                        } else {
                            typeInput.val('product');
                            productInput.val(itemId).attr('name', `items[${index}][product_id]`);
                            kitInput.val('').removeAttr('name');
                        }
                    });
                }
            });
        }

        // Agregar fila al repetidor de ítems
        function addItemRow() {
            const productOptions = $('#itemsBody tr:first-child select.item-selector').html();
            const row = `
                <tr class="item-row" data-index="${itemIndex}">
                    <td>
                        <input type="hidden" name="items[${itemIndex}][item_type]" class="row-item-type" value="product">
                        <input type="hidden" name="items[${itemIndex}][product_id]" class="row-product-id" value="">
                        <input type="hidden" name="items[${itemIndex}][kit_id]" class="row-kit-id" value="">
                        
                        <select class="form-control form-control-sm select2-item-selector item-selector" required style="width: 100%;">
                            ${productOptions}
                        </select>
                    </td>
                    <td>
                        <div class="input-group input-group-sm">
                            <input type="number" name="items[${itemIndex}][quantity]" class="form-control form-control-sm text-center row-quantity" min="1" value="1" required>
                            <div class="input-group-append">
                                <span class="input-group-text row-unit-badge text-muted text-xs bg-light">und</span>
                            </div>
                        </div>
                    </td>
                    <td>
                        <input type="text" name="items[${itemIndex}][notes]" class="form-control form-control-sm" placeholder="Especificaciones adicionales...">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-item" title="Eliminar fila">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                </tr>
            `;

            $('#itemsBody').append(row);
            const newRow = $('#itemsBody tr:last-child');
            initSelect2(newRow);
            
            // Forzar disparo del cambio para configurar nombres correctos
            newRow.find('.item-selector').val('').trigger('change');

            itemIndex++;
            updateRemoveButtons();
            updateTotalItemsCount();
        }

        function updateRemoveButtons() {
            const rows = $('#itemsBody tr.item-row').length;
            $('#itemsBody tr.item-row .remove-item').toggle(rows > 1);
        }

        function updateTotalItemsCount() {
            $('#totalItemsCount').text($('#itemsBody tr.item-row').length);
        }

        // Toggles del Modal de Producto Rápido
        function toggleModalKitFields() {
            const isKit = $('#modal_is_kit').is(':checked');
            if (isKit) {
                $('#productDetailsSection').slideUp(200);
                $('#kitDetailsSection').slideDown(200);
                $('#modal_price').prop('required', false);
                $('#modalComponentsTable input, #modalComponentsTable select').prop('required', true);
            } else {
                $('#kitDetailsSection').slideUp(200);
                $('#productDetailsSection').slideDown(200);
                $('#modalComponentsTable input, #modalComponentsTable select').prop('required', false);
            }
        }

        function toggleModalGenericFields() {
            const isGeneric = $('#modal_is_generic').is(':checked');
            if (isGeneric) {
                $('#modal_brand_id').closest('.form-group').slideUp(200);
                $('#modal_category_id').closest('.form-group').find('select').prop('required', false);
                $('#modal_location_id').closest('.form-group').find('select').prop('required', false);
                $('.modal-generic-marker').fadeOut(200);
            } else {
                $('#modal_brand_id').closest('.form-group').slideDown(200);
                $('#modal_category_id').closest('.form-group').find('select').prop('required', true);
                $('#modal_location_id').closest('.form-group').find('select').prop('required', true);
                $('.modal-generic-marker').fadeIn(200);
            }
        }

        // Componentes en el modal de kit
        let modalComponentIndex = 0;
        function addModalComponentRow() {
            const options = $('#modal_simple_products_source').html();
            const row = `
                <tr class="modal-comp-row">
                    <td>
                        <select name="components[${modalComponentIndex}][child_id]" class="form-control form-control-sm select2-modal-comp" required>
                            ${options}
                        </select>
                    </td>
                    <td>
                        <input type="number" name="components[${modalComponentIndex}][quantity]" class="form-control form-control-sm text-center" min="1" value="1" required>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-link text-danger remove-modal-comp" title="Eliminar"><i class="fas fa-times-circle"></i></button>
                    </td>
                </tr>
            `;
            $('#modalComponentsBody').append(row);
            
            // Inicializar Select2 con dropdownParent
            $('#modalComponentsBody tr:last-child .select2-modal-comp').select2({
                theme: 'bootstrap4',
                width: '100%',
                dropdownParent: $('#quickItemModal')
            });

            modalComponentIndex++;
            $('#noComponentsPlaceholder').hide();
        }

        $(document).on('click', '.remove-modal-comp', function() {
            $(this).closest('tr').remove();
            if ($('#modalComponentsBody tr').length === 0) {
                $('#noComponentsPlaceholder').show();
            }
        });

        // Al guardar nuevo ítem rápido (AJAX)
        $('#quickItemForm').on('submit', function(e) {
            e.preventDefault();
            
            const isKit = $('#modal_is_kit').is(':checked');
            const url = isKit ? '{{ route("admin.products.quick-store-kit") }}' : '{{ route("admin.products.quick-store") }}';
            const btn = $('#saveQuickItemBtn');
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Guardando...');

            $.ajax({
                url: url,
                method: 'POST',
                data: $(this).serialize(),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#quickItemModal').modal('hide');
                    
                    const product = response.product;
                    const isKitNum = isKit ? 1 : 0;
                    const badgeText = isKit ? ' [Kit]' : '';
                    
                    // Inyectar el nuevo producto a todos los dropdowns de la RFQ
                    const newOption = `<option value="${product.id}" data-is-kit="${isKitNum}" data-unit="${product.unit ? product.unit.abbreviation : 'und'}">${product.name} (${product.code ?? 'S/C'})${badgeText}</option>`;
                    
                    $('.item-selector').each(function() {
                        const currentVal = $(this).val();
                        if (isKit) {
                            $(this).find('optgroup[label="Kits / Compuestos"]').append(newOption);
                        } else {
                            $(this).find('optgroup[label="Productos Individuales"]').append(newOption);
                            $('#modal_simple_products_source').append(`<option value="${product.id}">${product.name} (${product.code ?? 'S/C'})</option>`);
                        }
                        $(this).val(currentVal).trigger('change.select2');
                    });

                    // Seleccionar automáticamente en la fila activa
                    if (activeRowForModal) {
                        activeRowForModal.find('.item-selector').val(product.id).trigger('change');
                    }

                    Swal.fire({
                        icon: 'success',
                        title: '¡Creado!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors || {};
                        if (errors.code) {
                            $('#modalCodeError').show();
                            Swal.fire({
                                icon: 'warning',
                                title: 'Código Duplicado',
                                text: 'El código ingresado ya está asignado a otro producto.'
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error de Validación',
                                text: xhr.responseJSON.message || 'Complete los campos obligatorios.'
                            });
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Hubo un error inesperado al guardar el ítem.'
                        });
                    }
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar Ítem');
                }
            });
        });

        // Configuración de eventos del modal
        $('#modal_is_kit').on('change', toggleModalKitFields);
        $('#modal_is_generic').on('change', toggleModalGenericFields);
        $('#addModalComponentBtn').on('click', addModalComponentRow);

        $('#quickItemModal').on('hidden.bs.modal', function() {
            $('#quickItemForm')[0].reset();
            $('#modalCodeError').hide();
            $('#quickItemModal .select2').val('').trigger('change');
            $('#modalComponentsBody').empty();
            $('#noComponentsPlaceholder').show();
            $('#modal_is_kit').prop('checked', false).trigger('change');
            $('#modal_is_generic').prop('checked', false).trigger('change');
            activeRowForModal = null;
        });

        $('#modal_code').on('blur', function() {
            const code = $(this).val();
            if (code) {
                $.get('{{ route("admin.products.search") }}', { search: code }, function(products) {
                    const exists = products.some(p => p.code.toLowerCase() === code.toLowerCase());
                    $('#modalCodeError').toggle(exists);
                });
            }
        });

        $(document).ready(function() {
            // Inicializar Select2 en los elementos existentes
            initSelect2();
            
            // Sincronizar inputs iniciales de filas cargadas
            $('#itemsBody tr.item-row').each(function() {
                const select = $(this).find('.item-selector');
                const selectedOpt = select.find('option:selected');
                const isKit = selectedOpt.data('is-kit') == 1;
                const itemId = select.val();
                const unit = selectedOpt.data('unit') || 'und';
                $(this).find('.row-unit-badge').text(unit);

                const index = $(this).data('index');
                const typeInput = $(this).find('.row-item-type');
                const productInput = $(this).find('.row-product-id');
                const kitInput = $(this).find('.row-kit-id');

                if (itemId !== "") {
                    if (isKit) {
                        typeInput.val('kit');
                        kitInput.val(itemId).attr('name', `items[${index}][kit_id]`);
                        productInput.val('').removeAttr('name');
                    } else {
                        typeInput.val('product');
                        productInput.val(itemId).attr('name', `items[${index}][product_id]`);
                        kitInput.val('').removeAttr('name');
                    }
                }
            });

            updateRemoveButtons();

            $('#addItemRowBtn').on('click', addItemRow);

            $(document).on('click', '.remove-item', function() {
                $(this).closest('tr').remove();
                updateRemoveButtons();
                updateTotalItemsCount();
            });

            // Abrir modal y rastrear cuál fila abrió
            $('#quickNewItemBtn').on('click', function() {
                const lastRow = $('#itemsBody tr.item-row:last-child');
                const lastSelect = lastRow.find('.item-selector');
                if (lastSelect.val() === "") {
                    activeRowForModal = lastRow;
                } else {
                    addItemRow();
                    activeRowForModal = $('#itemsBody tr.item-row:last-child');
                }
                $('#quickItemModal').modal('show');
            });

            // Submit y confirmación de la RFQ
            $('#saveRfqBtn').on('click', function(e) {
                e.preventDefault();

                let valid = true;
                if ($('#title').val().trim() === "") {
                    $('#title').addClass('is-invalid');
                    valid = false;
                } else {
                    $('#title').removeClass('is-invalid');
                }

                $('.item-selector').each(function() {
                    if ($(this).val() === "") {
                        $(this).addClass('is-invalid');
                        valid = false;
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });

                if (!valid) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Campos requeridos vacíos',
                        text: 'Por favor complete todos los campos obligatorios del formulario.'
                    });
                    return;
                }

                confirmAction({
                    title: 'Actualizar Solicitud de Cotización',
                    message: '¿Está seguro de guardar los cambios de esta Solicitud (RFQ)?',
                    alert: 'Se actualizarán las especificaciones y la lista de productos.',
                    confirmBtnClass: 'btn-warning',
                    onConfirm: function() {
                        $('#rfqForm').submit();
                    }
                });
            });
        });
    </script>
    @include('admin.partials.confirm-action')
@endsection
