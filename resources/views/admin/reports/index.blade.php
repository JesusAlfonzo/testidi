@extends('adminlte::page')

@section('title', 'Generador de Reportes')

@section('plugins.Select2', true)

@section('css')
<style>
    .results-table th {
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }
    .results-table td {
        font-size: 0.875rem;
        vertical-align: middle !important;
    }
    .filter-card {
        border-top: 3px solid #007bff;
    }
    .preview-card {
        border-top: 3px solid #28a745;
    }
</style>
@stop

@section('content_header')
    <h1><i class="fas fa-chart-line text-primary mr-1"></i> Generador Dinámico de Reportes</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            @include('admin.partials.session-messages')

            {{-- 1. FORMULARIO DE FILTROS --}}
            <div class="card filter-card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title text-primary font-weight-bold mb-0">
                        <i class="fas fa-filter mr-1"></i> Parámetros de Consulta
                    </h5>
                </div>
                <div class="card-body">
                    <form id="reportForm" action="{{ route('admin.reports.index') }}" method="GET">
                        <div class="row">
                            {{-- Tipo de Reporte --}}
                            <div class="col-md-4 form-group">
                                <label for="report_type" class="small font-weight-bold text-muted text-uppercase mb-1">Tipo de Reporte (*)</label>
                                <select name="report_type" id="report_type" class="form-control form-control-sm select2 @error('report_type') is-invalid @enderror" required>
                                    <option value="">Seleccione...</option>
                                    <option value="inventario" {{ (old('report_type', $filters['report_type'] ?? '') == 'inventario') ? 'selected' : '' }}>1. Inventario Actual</option>
                                    <option value="entradas" {{ (old('report_type', $filters['report_type'] ?? '') == 'entradas') ? 'selected' : '' }}>2. Historial de Entradas (Ingresos)</option>
                                    <option value="salidas" {{ (old('report_type', $filters['report_type'] ?? '') == 'salidas') ? 'selected' : '' }}>3. Historial de Salidas (Despachos)</option>
                                    <option value="fraccionamientos" {{ (old('report_type', $filters['report_type'] ?? '') == 'fraccionamientos') ? 'selected' : '' }}>4. Movimientos por Fraccionamiento</option>
                                </select>
                                @error('report_type')<span class="invalid-feedback d-block small">{{ $message }}</span>@enderror
                            </div>

                            {{-- Fecha Inicio --}}
                            <div class="col-md-4 form-group">
                                <label for="fecha_inicio" class="small font-weight-bold text-muted text-uppercase mb-1">Fecha Desde</label>
                                <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control form-control-sm @error('fecha_inicio') is-invalid @enderror" value="{{ old('fecha_inicio', $filters['fecha_inicio'] ?? '') }}">
                                @error('fecha_inicio')<span class="invalid-feedback d-block small">{{ $message }}</span>@enderror
                            </div>

                            {{-- Fecha Fin --}}
                            <div class="col-md-4 form-group">
                                <label for="fecha_fin" class="small font-weight-bold text-muted text-uppercase mb-1">Fecha Hasta</label>
                                <input type="date" name="fecha_fin" id="fecha_fin" class="form-control form-control-sm @error('fecha_fin') is-invalid @enderror" value="{{ old('fecha_fin', $filters['fecha_fin'] ?? '') }}">
                                @error('fecha_fin')<span class="invalid-feedback d-block small">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <div class="row mt-2">
                            {{-- Filtrar por Producto --}}
                            <div class="col-md-4 form-group">
                                <label for="product_id" class="small font-weight-bold text-muted text-uppercase mb-1">Filtrar por Producto</label>
                                <select name="product_id" id="product_id" class="form-control form-control-sm select2">
                                    <option value="">Todos los productos...</option>
                                    @foreach($products as $prod)
                                        <option value="{{ $prod->id }}" {{ (old('product_id', $filters['product_id'] ?? '') == $prod->id) ? 'selected' : '' }}>
                                            {{ $prod->name }} ({{ $prod->code ?? 'N/A' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Filtrar por Lote --}}
                            <div class="col-md-4 form-group">
                                <label for="batch_number" class="small font-weight-bold text-muted text-uppercase mb-1">Filtrar por Lote (Texto)</label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light"><i class="fas fa-barcode"></i></span>
                                    </div>
                                    <input type="text" name="batch_number" id="batch_number" class="form-control" placeholder="Escriba código de lote..." value="{{ old('batch_number', $filters['batch_number'] ?? '') }}">
                                </div>
                            </div>

                            {{-- Filtrar por Usuario --}}
                            <div class="col-md-4 form-group">
                                <label for="user_id" class="small font-weight-bold text-muted text-uppercase mb-1">Filtrar por Responsable/Usuario</label>
                                <select name="user_id" id="user_id" class="form-control form-control-sm select2">
                                    <option value="">Todos los usuarios...</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ (old('user_id', $filters['user_id'] ?? '') == $user->id) ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                        </div>

                        {{-- Filtros específicos de Inventario --}}
                        <div id="inventarioFilters" class="row mt-2 {{ (old('report_type', $filters['report_type'] ?? '') == 'inventario') ? '' : 'd-none' }}">
                            {{-- Ubicación --}}
                            <div class="col-md-4 form-group">
                                <label for="location_id" class="small font-weight-bold text-muted text-uppercase mb-1">Ubicación / Depósito</label>
                                <select name="location_id" id="location_id" class="form-control form-control-sm select2">
                                    <option value="">Todas las ubicaciones...</option>
                                    @foreach($locations as $loc)
                                        <option value="{{ $loc->id }}" {{ (old('location_id', $filters['location_id'] ?? '') == $loc->id) ? 'selected' : '' }}>
                                            {{ $loc->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Estado (is_active) --}}
                            <div class="col-md-4 form-group">
                                <label for="is_active" class="small font-weight-bold text-muted text-uppercase mb-1">Estado de Productos</label>
                                <select name="is_active" id="is_active" class="form-control form-control-sm select2">
                                    <option value="active" {{ (old('is_active', $filters['is_active'] ?? '') == 'active') ? 'selected' : '' }}>Solo Activos (Por defecto)</option>
                                    <option value="inactive" {{ (old('is_active', $filters['is_active'] ?? '') == 'inactive') ? 'selected' : '' }}>Solo Inactivos</option>
                                    <option value="all" {{ (old('is_active', $filters['is_active'] ?? '') == 'all') ? 'selected' : '' }}>Todos</option>
                                </select>
                            </div>

                            {{-- Origen --}}
                            <div class="col-md-4 form-group">
                                <label for="origin" class="small font-weight-bold text-muted text-uppercase mb-1">Origen del Registro</label>
                                <select name="origin" id="origin" class="form-control form-control-sm select2">
                                    <option value="all" {{ (old('origin', $filters['origin'] ?? '') == 'all') ? 'selected' : '' }}>Todos (Por defecto)</option>
                                    <option value="standard" {{ (old('origin', $filters['origin'] ?? '') == 'standard') ? 'selected' : '' }}>Estándar (Cargados por sistema)</option>
                                    <option value="on_the_fly" {{ (old('origin', $filters['origin'] ?? '') == 'on_the_fly') ? 'selected' : '' }}>Creados sobre la marcha</option>
                                </select>
                            </div>

                            {{-- Filtro de Stock (Operador + Valor) --}}
                            <div class="col-md-4 form-group">
                                <label for="stock_operator" class="small font-weight-bold text-muted text-uppercase mb-1">Filtro de Stock</label>
                                <div class="input-group input-group-sm">
                                    <select name="stock_operator" id="stock_operator" class="form-control col-md-4">
                                        <option value="">Operador...</option>
                                        <option value=">" {{ (old('stock_operator', $filters['stock_operator'] ?? '') == '>') ? 'selected' : '' }}>Mayor que (>)</option>
                                        <option value="<" {{ (old('stock_operator', $filters['stock_operator'] ?? '') == '<') ? 'selected' : '' }}>Menor que (<)</option>
                                        <option value="=" {{ (old('stock_operator', $filters['stock_operator'] ?? '') == '=') ? 'selected' : '' }}>Igual a (=)</option>
                                        <option value=">=" {{ (old('stock_operator', $filters['stock_operator'] ?? '') == '>=') ? 'selected' : '' }}>Mayor o igual (>=)</option>
                                        <option value="<=" {{ (old('stock_operator', $filters['stock_operator'] ?? '') == '<=') ? 'selected' : '' }}>Menor o igual (<=)</option>
                                    </select>
                                    <input type="number" name="stock_value" id="stock_value" class="form-control" placeholder="Valor de stock..." value="{{ old('stock_value', $filters['stock_value'] ?? '') }}" min="0">
                                </div>
                            </div>

                            {{-- Vence Desde --}}
                            <div class="col-md-4 form-group">
                                <label for="expiry_from" class="small font-weight-bold text-muted text-uppercase mb-1">Vencimiento Desde</label>
                                <input type="date" name="expiry_from" id="expiry_from" class="form-control form-control-sm @error('expiry_from') is-invalid @enderror" value="{{ old('expiry_from', $filters['expiry_from'] ?? '') }}">
                                @error('expiry_from')<span class="invalid-feedback d-block small">{{ $message }}</span>@enderror
                            </div>

                            {{-- Vence Hasta --}}
                            <div class="col-md-4 form-group">
                                <label for="expiry_to" class="small font-weight-bold text-muted text-uppercase mb-1">Vencimiento Hasta</label>
                                <input type="date" name="expiry_to" id="expiry_to" class="form-control form-control-sm @error('expiry_to') is-invalid @enderror" value="{{ old('expiry_to', $filters['expiry_to'] ?? '') }}">
                                @error('expiry_to')<span class="invalid-feedback d-block small">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('admin.reports.index') }}" class="btn btn-default mr-2">
                                <i class="fas fa-undo mr-1"></i> Resetear
                            </a>
                            <button type="submit" name="action" value="preview" class="btn btn-info mr-2">
                                <i class="fas fa-search mr-1"></i> Previsualizar Datos
                            </button>
                            <button type="button" id="exportPdfBtn" class="btn btn-danger">
                                <i class="fas fa-file-pdf mr-1"></i> Exportar PDF
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- 2. SECCIÓN DE RESULTADOS (PREVISUALIZACIÓN) --}}
            @if(isset($data))
                <div class="card preview-card shadow-sm mt-4">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="card-title text-success font-weight-bold mb-0">
                            <i class="fas fa-table mr-1"></i> Previsualización de Datos
                        </h5>
                        <span class="badge badge-success px-3 py-2 font-weight-bold">
                            {{ $data->count() }} Registros Encontrados
                        </span>
                    </div>
                    <div class="card-body p-0">
                        @if($data->isEmpty())
                            <div class="text-center p-5 text-muted">
                                <i class="fas fa-info-circle fa-2x mb-3 text-secondary"></i>
                                <p class="mb-0">No se encontraron resultados que coincidan con los criterios de búsqueda seleccionados.</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm table-striped table-hover results-table mb-0">
                                    <thead class="thead-light">
                                        @if($filters['report_type'] == 'inventario')
                                            <tr>
                                                <th class="pl-3 py-3 text-uppercase text-muted">SKU / Código</th>
                                                <th class="py-3 text-uppercase text-muted">Producto</th>
                                                <th class="py-3 text-uppercase text-muted">Categoría</th>
                                                <th class="py-3 text-uppercase text-muted">Ubicación</th>
                                                <th class="py-3 text-uppercase text-muted">Stock</th>
                                                <th class="py-3 text-uppercase text-muted">U. Medida</th>
                                                <th class="py-3 text-uppercase text-muted">Min. Stock</th>
                                                <th class="py-3 text-uppercase text-muted">Costo Unit.</th>
                                                <th class="pr-3 py-3 text-uppercase text-muted">Precio Sug.</th>
                                            </tr>
                                        @elseif($filters['report_type'] == 'entradas')
                                            <tr>
                                                <th class="pl-3 py-3 text-uppercase text-muted">Fecha</th>
                                                <th class="py-3 text-uppercase text-muted">Documento</th>
                                                <th class="py-3 text-uppercase text-muted">Producto</th>
                                                <th class="py-3 text-uppercase text-muted">Lote / Expiración</th>
                                                <th class="py-3 text-uppercase text-muted">Cant. Recibida</th>
                                                <th class="py-3 text-uppercase text-muted">Costo Unit.</th>
                                                <th class="py-3 text-uppercase text-muted">Total</th>
                                                <th class="py-3 text-uppercase text-muted">Proveedor</th>
                                                <th class="pr-3 py-3 text-uppercase text-muted">Recibido por</th>
                                            </tr>
                                        @elseif($filters['report_type'] == 'salidas')
                                            <tr>
                                                <th class="pl-3 py-3 text-uppercase text-muted">Fecha</th>
                                                <th class="py-3 text-uppercase text-muted">Nro Solicitud</th>
                                                <th class="py-3 text-uppercase text-muted">Producto/Kit</th>
                                                <th class="py-3 text-uppercase text-muted">Cantidad</th>
                                                <th class="py-3 text-uppercase text-muted">Justificación</th>
                                                <th class="py-3 text-uppercase text-muted">Solicitante</th>
                                                <th class="pr-3 py-3 text-uppercase text-muted">Autorizador</th>
                                            </tr>
                                        @elseif($filters['report_type'] == 'fraccionamientos')
                                            <tr>
                                                <th class="pl-3 py-3 text-uppercase text-muted">Fecha / Hora</th>
                                                <th class="py-3 text-uppercase text-muted">Producto Afectado</th>
                                                <th class="py-3 text-uppercase text-muted">Acción</th>
                                                <th class="py-3 text-uppercase text-muted">Cantidad</th>
                                                <th class="py-3 text-uppercase text-muted">Stock Final</th>
                                                <th class="py-3 text-uppercase text-muted">Usuario</th>
                                                <th class="pr-3 py-3 text-uppercase text-muted">Descripción / Bitácora</th>
                                            </tr>
                                        @endif
                                    </thead>
                                    <tbody>
                                        @if($filters['report_type'] == 'inventario')
                                            @foreach($data as $product)
                                                <tr>
                                                    <td class="pl-3 font-weight-bold text-muted">{{ $product->code ?? 'N/A' }}</td>
                                                    <td>{{ $product->name }}</td>
                                                    <td>{{ $product->category->name ?? 'N/A' }}</td>
                                                    <td>{{ $product->location->name ?? 'N/A' }}</td>
                                                    <td class="font-weight-bold">
                                                        <span class="badge badge-{{ $product->stock <= $product->min_stock ? 'danger' : 'success' }} px-2 py-1">
                                                            {{ $product->stock }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $product->unit->name ?? 'N/A' }}</td>
                                                    <td>{{ $product->min_stock }}</td>
                                                    <td>${{ number_format($product->cost, 2) }}</td>
                                                    <td class="pr-3 font-weight-bold text-primary">${{ number_format($product->price, 2) }}</td>
                                                </tr>
                                            @endforeach
                                        @elseif($filters['report_type'] == 'entradas')
                                            @foreach($data as $item)
                                                <tr>
                                                    <td class="pl-3">{{ $item->stockIn->entry_date ? $item->stockIn->entry_date->format('d/m/Y') : $item->created_at->format('d/m/Y') }}</td>
                                                    <td class="font-weight-bold text-muted">{{ $item->stockIn->document_type }}: {{ $item->stockIn->document_number }}</td>
                                                    <td>{{ $item->product->name }}</td>
                                                    <td>
                                                        <span class="d-block font-weight-bold">Lote: {{ $item->batch_number ?? 'N/A' }}</span>
                                                        @if($item->expiration_date)
                                                            <small class="text-danger font-weight-bold"><i class="far fa-calendar-times mr-1"></i>Vence: {{ $item->expiration_date->format('d/m/Y') }}</small>
                                                        @else
                                                            <small class="text-muted">Sin expiración</small>
                                                        @endif
                                                    </td>
                                                    <td class="font-weight-bold text-success">+{{ $item->quantity }}</td>
                                                    <td>${{ number_format($item->unit_cost, 2) }}</td>
                                                    <td class="font-weight-bold">${{ number_format($item->quantity * $item->unit_cost, 2) }}</td>
                                                    <td>{{ $item->stockIn->supplier->name ?? 'Ajuste interno' }}</td>
                                                    <td class="pr-3 text-muted">{{ $item->stockIn->user->name ?? 'N/A' }}</td>
                                                </tr>
                                            @endforeach
                                        @elseif($filters['report_type'] == 'salidas')
                                            @foreach($data as $item)
                                                <tr>
                                                    <td class="pl-3">{{ $item->request->processed_at ? $item->request->processed_at->format('d/m/Y H:i') : $item->created_at->format('d/m/Y H:i') }}</td>
                                                    <td class="font-weight-bold text-muted">REQ-{{ $item->request->id }}</td>
                                                    <td>
                                                        @if($item->item_type == 'kit' && $item->kit)
                                                            <span class="badge badge-purple text-purple px-2 py-1"><i class="fas fa-cubes mr-1"></i>Kit: {{ $item->kit->name }}</span>
                                                            <small class="d-block text-muted">Componente: {{ $item->product->name ?? 'N/A' }}</small>
                                                        @else
                                                            {{ $item->product->name }}
                                                        @endif
                                                    </td>
                                                    <td class="font-weight-bold text-danger">-{{ $item->quantity_requested }}</td>
                                                    <td class="text-muted">{{ Str::limit($item->request->justification, 50) }}</td>
                                                    <td>{{ $item->request->requester->name ?? 'N/A' }}</td>
                                                    <td class="pr-3 text-muted">{{ $item->request->approver->name ?? 'N/A' }}</td>
                                                </tr>
                                            @endforeach
                                        @elseif($filters['report_type'] == 'fraccionamientos')
                                            @foreach($data as $activity)
                                                @php
                                                    $props = $activity->properties;
                                                    $qty = $props['quantity'] ?? 0;
                                                    $type = $props['type'] ?? 'out';
                                                @endphp
                                                <tr>
                                                    <td class="pl-3">{{ $activity->created_at->format('d/m/Y H:i:s') }}</td>
                                                    <td class="font-weight-bold">{{ $activity->subject->name ?? 'Producto Eliminado' }}</td>
                                                    <td>
                                                        @if($type == 'out')
                                                            <span class="badge badge-warning"><i class="fas fa-sign-out-alt mr-1"></i>Origen (Empaque)</span>
                                                        @else
                                                            <span class="badge badge-success"><i class="fas fa-sign-in-alt mr-1"></i>Destino (Unidad)</span>
                                                        @endif
                                                    </td>
                                                    <td class="font-weight-bold text-{{ $type == 'in' ? 'success' : 'danger' }}">
                                                        {{ $type == 'in' ? '+' : '-' }}{{ $qty }}
                                                    </td>
                                                    <td class="font-weight-bold text-muted">{{ $props['stock_after'] ?? 'N/A' }}</td>
                                                    <td>{{ $activity->causer->name ?? 'Sistema' }}</td>
                                                    <td class="pr-3 text-muted" style="max-width: 300px;">{{ $activity->description }}</td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                    <tfoot class="bg-light font-weight-bold">
                                        @if($filters['report_type'] == 'inventario')
                                            <tr>
                                                <td colspan="4" class="pl-3 text-right text-uppercase text-muted small">Total Registros: {{ $totals['count'] }} | Totales:</td>
                                                <td>
                                                    <span class="badge badge-info px-2 py-1">
                                                        {{ $totals['sum_quantity'] }}
                                                    </span>
                                                </td>
                                                <td colspan="2"></td>
                                                <td class="text-primary">${{ number_format($totals['sum_amount'], 2) }}</td>
                                                <td class="pr-3"></td>
                                            </tr>
                                        @elseif($filters['report_type'] == 'entradas')
                                            <tr>
                                                <td colspan="4" class="pl-3 text-right text-uppercase text-muted small">Total Registros: {{ $totals['count'] }} | Totales:</td>
                                                <td class="text-success font-weight-bold">+{{ $totals['sum_quantity'] }}</td>
                                                <td></td>
                                                <td class="font-weight-bold">${{ number_format($totals['sum_amount'], 2) }}</td>
                                                <td colspan="2" class="pr-3"></td>
                                            </tr>
                                        @elseif($filters['report_type'] == 'salidas')
                                            <tr>
                                                <td colspan="3" class="pl-3 text-right text-uppercase text-muted small">Total Registros: {{ $totals['count'] }} | Totales:</td>
                                                <td class="text-danger font-weight-bold">-{{ $totals['sum_quantity'] }}</td>
                                                <td colspan="3" class="pr-3"></td>
                                            </tr>
                                        @elseif($filters['report_type'] == 'fraccionamientos')
                                            <tr>
                                                <td colspan="3" class="pl-3 text-right text-uppercase text-muted small">Total Registros: {{ $totals['count'] }} | Totales:</td>
                                                <td>{{ $totals['sum_quantity'] }}</td>
                                                <td colspan="3" class="pr-3"></td>
                                            </tr>
                                        @endif
                                    </tfoot>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Inicializar Select2
            $('.select2').select2({
                theme: 'bootstrap4',
                width: '100%'
            });

            // Función para mostrar/ocultar filtros dinámicamente según el tipo de reporte
            function toggleReportFilters() {
                const reportType = $('#report_type').val();
                if (reportType === 'inventario') {
                    $('#inventarioFilters').removeClass('d-none');
                } else {
                    $('#inventarioFilters').addClass('d-none');
                    // Limpiar valores de filtros específicos para no enviarlos si no aplica
                    $('#location_id').val('').trigger('change');
                    $('#is_active').val('active').trigger('change');
                    $('#origin').val('all').trigger('change');
                    $('#stock_operator').val('');
                    $('#stock_value').val('');
                    $('#expiry_from').val('');
                    $('#expiry_to').val('');
                }
            }

            // Escuchar cambios en el selector de tipo de reporte
            $('#report_type').on('change', function() {
                toggleReportFilters();
            });

            // Ejecutar al cargar la página para mantener el estado
            toggleReportFilters();

            // Manejo de exportación a PDF (Envía formulario mediante POST a otra ruta)
            $('#exportPdfBtn').on('click', function(e) {
                e.preventDefault();
                
                // Cambiar el action y el method del formulario temporalmente
                const form = $('#reportForm');
                const originalAction = form.attr('action');
                const originalMethod = form.attr('method');
                
                // Creamos un input temporal para indicar que es exportación
                const pdfActionInput = $('<input>').attr({
                    type: 'hidden',
                    name: 'action',
                    value: 'pdf'
                });
                form.append(pdfActionInput);
                
                form.attr('action', "{{ route('admin.reports.export') }}");
                form.attr('method', 'POST');
                
                // Agregar el token CSRF para el POST
                const csrfInput = $('<input>').attr({
                    type: 'hidden',
                    name: '_token',
                    value: "{{ csrf_token() }}"
                });
                form.append(csrfInput);
                
                // Enviar formulario
                form.submit();
                
                // Restaurar formulario para futuras previsualizaciones
                form.attr('action', originalAction);
                form.attr('method', originalMethod);
                pdfActionInput.remove();
                csrfInput.remove();
            });
        });
    </script>
@endsection
