@extends('adminlte::page')

@section('title', 'RFQ ' . $rfq->code)

@section('plugins.Select2', true)
@section('plugins.Sweetalert2', true)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">
            <i class="fas fa-file-invoice text-info mr-2"></i>
            RFQ {{ $rfq->code }}
        </h1>
        <div class="d-flex align-items-center">
            <a href="{{ route('admin.rfq.pdf', $rfq) }}"
               class="btn btn-sm btn-outline-secondary mr-2" target="_blank">
                <i class="fas fa-file-pdf mr-1"></i> PDF
            </a>
            @if($rfq->isEditable())
                @can('rfq_editar')
                    <a href="{{ route('admin.rfq.edit', $rfq) }}" class="btn btn-sm btn-warning">
                        <i class="fas fa-edit mr-1"></i> Editar RFQ
                    </a>
                @endcan
            @endif
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    @include('admin.partials.session-messages')

    <div class="row">

        {{-- ============================================================
             COLUMNA PRINCIPAL — 70%
             ============================================================ --}}
        <div class="col-lg-9 col-md-12">

            {{-- Aviso: OC ya generada --}}
            @if($rfq->purchaseOrder)
                <div class="alert alert-success border-0 shadow-sm mb-4" style="border-radius: 12px;">
                    <i class="fas fa-check-circle mr-2"></i>
                    Esta RFQ ya fue convertida a la Orden de Compra
                    <a href="{{ route('admin.purchaseOrders.show', $rfq->purchaseOrder) }}"
                       class="alert-link font-weight-bold">
                        {{ $rfq->purchaseOrder->code }}
                    </a>.
                </div>
            @endif

            {{-- --------------------------------------------------------
                 CARD: Formulario — Seleccionar Proveedor + Ítems + Botón
                 -------------------------------------------------------- --}}
            @if(!$rfq->purchaseOrder && in_array($rfq->status, ['sent', 'closed']))
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; overflow: hidden;">
                    <div class="card-header border-bottom py-3"
                         style="background: linear-gradient(135deg, #1a73e8 0%, #0d47a1 100%);">
                        <h3 class="card-title font-weight-bold mb-0 text-white">
                            <i class="fas fa-shopping-cart mr-2"></i>
                            Generar Orden de Compra
                        </h3>
                    </div>
                    <div class="card-body">

                        <form method="POST"
                              action="{{ route('admin.rfq.convert-to-po', $rfq) }}"
                              id="convertPoForm">
                            @csrf

                            {{-- PASO 1: Proveedor --}}
                            <div class="form-group">
                                <label for="supplier_id_show"
                                       class="font-weight-bold text-sm text-dark">
                                    <span class="badge badge-primary mr-1">1</span>
                                    Proveedor definitivo
                                    <span class="text-danger">*</span>
                                </label>
                                <select name="supplier_id"
                                        id="supplier_id_show"
                                        class="form-control select2-supplier"
                                        style="width: 100%;"
                                        required>
                                    <option value="">— Seleccione el proveedor ganador —</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- PASO 2: Tabla de ítems con exención --}}
                            <label class="font-weight-bold text-sm text-dark mt-2">
                                <span class="badge badge-primary mr-1">2</span>
                                Ítems — marque los exentos de IVA (16%)
                            </label>
                            <div class="table-responsive mt-2">
                                <table class="table table-bordered table-sm align-middle mb-0"
                                       id="showItemsTable">
                                    <thead style="background: #f4f6f9; font-size: 11px; font-weight: 700;">
                                        <tr class="text-uppercase text-muted">
                                            <th style="width: 4%">#</th>
                                            <th style="width: 44%">Producto / Kit</th>
                                            <th style="width: 10%" class="text-center">Cant.</th>
                                            <th style="width: 10%" class="text-center">Unidad</th>
                                            <th style="width: 16%">Notas</th>
                                            <th style="width: 16%" class="text-center">
                                                ¿Exento IVA?
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($rfq->items as $index => $item)
                                            <tr>
                                                <td class="text-center text-muted text-sm">
                                                    {{ $index + 1 }}
                                                </td>
                                                <td>
                                                    @if($item->item_type === 'kit')
                                                        <strong>{{ $item->kit->name ?? 'Kit' }}</strong>
                                                        <span class="badge badge-info ml-1">Kit</span>
                                                    @else
                                                        <strong>{{ $item->product->name ?? '—' }}</strong>
                                                        <br>
                                                        <small class="text-muted">
                                                            {{ $item->product->code ?? 'S/C' }}
                                                        </small>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-primary">
                                                        {{ $item->quantity }}
                                                    </span>
                                                </td>
                                                <td class="text-center text-sm text-muted">
                                                    @if($item->item_type === 'kit')
                                                        kit
                                                    @else
                                                        {{ $item->product->unit->abbreviation ?? 'und' }}
                                                    @endif
                                                </td>
                                                <td class="text-sm text-muted">
                                                    {{ $item->notes ?? '—' }}
                                                </td>
                                                <td class="text-center align-middle">
                                                    <input type="hidden"
                                                           name="pre_exempt[{{ $index }}]"
                                                           value="0">
                                                    <input type="checkbox"
                                                           name="pre_exempt[{{ $index }}]"
                                                           value="1"
                                                           class="show-exempt-check"
                                                           {{ $item->is_exempt ? 'checked' : '' }}
                                                           style="width: 18px; height: 18px; cursor: pointer;"
                                                           title="Marcar como exento de IVA">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="alert alert-warning border-0 mt-3 mb-3 py-2 px-3 text-sm"
                                 style="border-radius: 8px;">
                                <i class="fas fa-info-circle mr-1"></i>
                                Los ítems <strong>marcados como Exentos</strong> no acumularán
                                el 16% de IVA en la Orden de Compra generada.
                            </div>

                            <button type="submit"
                                    id="btnConvertToPo"
                                    class="btn btn-success btn-block shadow font-weight-bold"
                                    style="border-radius: 8px; padding: 12px;">
                                <i class="fas fa-shopping-cart mr-2"></i>
                                Procesar Orden de Compra
                            </button>
                        </form>

                    </div>
                </div>
            @endif

            {{-- --------------------------------------------------------
                 CARD: Ítems requeridos — solo lectura
                 -------------------------------------------------------- --}}
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                <div class="card-header border-bottom py-3"
                     style="background: linear-gradient(180deg, #ffffff 0%, #f9fafb 100%);">
                    <h3 class="card-title text-dark font-weight-bold mb-0">
                        <i class="fas fa-boxes text-info mr-2"></i>
                        Ítems Requeridos
                    </h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-muted text-uppercase"
                               style="font-size: 11px; font-weight: 700;">
                            <tr>
                                <th style="width: 4%">#</th>
                                <th style="width: 14%">Código</th>
                                <th style="width: 32%">Producto / Kit</th>
                                <th style="width: 14%">Categoría</th>
                                <th style="width: 9%" class="text-center">Cantidad</th>
                                <th style="width: 12%" class="text-center">Estado IVA</th>
                                <th style="width: 15%">Notas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rfq->items as $index => $item)
                                <tr>
                                    <td class="text-muted text-sm">{{ $index + 1 }}</td>
                                    @if($item->item_type === 'kit')
                                        <td class="text-muted">S/C</td>
                                        <td>
                                            @if($item->kit)
                                                <a href="{{ route('admin.kits.show', $item->kit_id) }}"
                                                   class="font-weight-bold text-dark">
                                                    {{ $item->kit->name }}
                                                </a>
                                            @else
                                                <span class="font-weight-bold text-dark">
                                                    {{ $item->product->name ?? 'Kit' }}
                                                </span>
                                            @endif
                                            <span class="badge badge-info ml-1">Kit</span>
                                        </td>
                                        <td class="text-muted text-sm">Kits</td>
                                        <td class="text-center">
                                            <span class="badge badge-primary">
                                                {{ $item->quantity }}
                                            </span>
                                            <small class="text-muted ml-1">kit</small>
                                        </td>
                                    @else
                                        <td class="text-sm">{{ $item->product->code ?? 'N/A' }}</td>
                                        <td>
                                            @if($item->product)
                                                <a href="{{ route('admin.products.show', $item->product) }}"
                                                   class="font-weight-bold text-dark">
                                                    {{ $item->product->name }}
                                                </a>
                                                @if($item->product->is_kit)
                                                    <span class="badge badge-info ml-1">Kit</span>
                                                @endif
                                            @else
                                                <span class="text-danger font-weight-bold">
                                                    Producto Desconocido
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-sm text-muted">
                                            {{ $item->product->category->name ?? 'N/A' }}
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-primary">
                                                {{ $item->quantity }}
                                            </span>
                                            <small class="text-muted ml-1">
                                                {{ $item->product->unit->abbreviation ?? 'und' }}
                                            </small>
                                        </td>
                                    @endif
                                    <td class="text-center">
                                        @if($item->is_exempt)
                                            <span class="badge badge-success py-1 px-2">
                                                <i class="fas fa-check mr-1"></i>Exento
                                            </span>
                                        @else
                                            <span class="badge badge-secondary py-1 px-2">
                                                Gravado
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-sm text-muted">{{ $item->notes ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>{{-- /col-lg-9 --}}

        {{-- ============================================================
             COLUMNA LATERAL — 30%
             ============================================================ --}}
        <div class="col-lg-3 col-md-12">

            {{-- Resumen de RFQ --}}
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; overflow: hidden;">
                <div class="py-3 px-3"
                     style="background: linear-gradient(135deg, #17a2b8, #0a7fa3);">
                    <h3 class="card-title h6 text-white font-weight-bold mb-0">
                        <i class="fas fa-file-invoice-dollar mr-1"></i> Resumen de RFQ
                    </h3>
                </div>
                <div class="card-body p-3">
                    <table class="table table-sm table-borderless text-sm mb-0">
                        <tr>
                            <th class="pl-0 text-muted" style="width: 45%">Código:</th>
                            <td class="font-weight-bold">{{ $rfq->code }}</td>
                        </tr>
                        <tr>
                            <th class="pl-0 text-muted">Estatus:</th>
                            <td>{!! $rfq->status_badge !!}</td>
                        </tr>
                        @if($rfq->purchaseOrder)
                            <tr>
                                <th class="pl-0 text-muted">OC:</th>
                                <td>
                                    <a href="{{ route('admin.purchaseOrders.show', $rfq->purchaseOrder) }}"
                                       class="font-weight-bold text-success">
                                        {{ $rfq->purchaseOrder->code }}
                                    </a>
                                </td>
                            </tr>
                        @endif
                        <tr>
                            <th class="pl-0 text-muted">Creador:</th>
                            <td>{{ $rfq->creator->name ?? 'Sistema' }}</td>
                        </tr>
                        <tr>
                            <th class="pl-0 text-muted">Creación:</th>
                            <td>{{ $rfq->created_at->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <th class="pl-0 text-muted">Límite:</th>
                            <td>{{ $rfq->date_required?->format('d/m/Y') ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th class="pl-0 text-muted">Entrega:</th>
                            <td>{{ $rfq->delivery_deadline?->format('d/m/Y') ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th class="pl-0 text-muted">Ítems:</th>
                            <td>
                                <span class="badge badge-primary">
                                    {{ $rfq->items->count() }}
                                </span>
                            </td>
                        </tr>
                    </table>

                    @if($rfq->description)
                        <hr class="my-2">
                        <div style="font-size: 12px;">
                            <strong class="text-muted d-block">Descripción:</strong>
                            <p class="text-dark mb-0 font-italic">{{ $rfq->description }}</p>
                        </div>
                    @endif

                    @if($rfq->notes)
                        <hr class="my-2">
                        <div style="font-size: 12px;">
                            <strong class="text-muted d-block">Notas Internas:</strong>
                            <p class="text-dark mb-0 font-italic">{{ $rfq->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Acciones de Flujo de Trabajo --}}
            @can('rfq_enviar')
                @if(in_array($rfq->status, ['draft', 'sent']))
                    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                        <div class="card-header bg-light border-bottom py-2">
                            <h3 class="card-title text-dark font-weight-bold mb-0 text-sm">
                                <i class="fas fa-traffic-light text-warning mr-1"></i>
                                Flujo de Trabajo
                            </h3>
                        </div>
                        <div class="card-body p-3">
                            @if($rfq->status === 'draft')
                                <p class="text-xs text-muted mb-2">
                                    Marque como enviada para congelarla y poder cargar ofertas.
                                </p>
                                <button type="button"
                                        class="btn btn-success btn-block shadow-sm font-weight-bold mb-2"
                                        onclick="confirmAction({
                                            title: 'Enviar RFQ',
                                            message: '¿Marcar esta RFQ como ENVIADA?',
                                            alert: 'No podrá modificarse directamente una vez enviada.',
                                            confirmBtnClass: 'btn-success',
                                            onConfirm: function() {
                                                var f = document.createElement('form');
                                                f.method = 'POST';
                                                f.action = '{{ route('admin.rfq.mark-sent', $rfq) }}';
                                                f.innerHTML = '<input type=&quot;hidden&quot; name=&quot;_token&quot; value=&quot;' + document.querySelector('meta[name=&quot;csrf-token&quot;]').getAttribute('content') + '&quot;>';
                                                document.body.appendChild(f);
                                                f.submit();
                                            }
                                        })">
                                    <i class="fas fa-paper-plane mr-1"></i> Marcar como Enviada
                                </button>
                            @elseif($rfq->status === 'sent')
                                <p class="text-xs text-muted mb-2 font-italic">
                                    RFQ enviada. Puede cerrar formalmente el proceso.
                                </p>
                                <button type="button"
                                        class="btn btn-primary btn-block shadow-sm font-weight-bold mb-2"
                                        onclick="confirmAction({
                                            title: 'Cerrar RFQ',
                                            message: '¿CERRAR esta RFQ?',
                                            alert: 'Finalizará formalmente el proceso de cotización.',
                                            confirmBtnClass: 'btn-primary',
                                            onConfirm: function() {
                                                var f = document.createElement('form');
                                                f.method = 'POST';
                                                f.action = '{{ route('admin.rfq.mark-closed', $rfq) }}';
                                                f.innerHTML = '<input type=&quot;hidden&quot; name=&quot;_token&quot; value=&quot;' + document.querySelector('meta[name=&quot;csrf-token&quot;]').getAttribute('content') + '&quot;>';
                                                document.body.appendChild(f);
                                                f.submit();
                                            }
                                        })">
                                    <i class="fas fa-check-circle mr-1"></i> Cerrar Proceso RFQ
                                </button>
                            @endif

                            <button type="button"
                                    class="btn btn-outline-danger btn-block btn-sm"
                                    onclick="confirmAction({
                                        title: 'Cancelar RFQ',
                                        message: '¿CANCELAR esta RFQ por completo?',
                                        alert: 'Esta acción no se puede deshacer.',
                                        confirmBtnClass: 'btn-danger',
                                        onConfirm: function() {
                                            var f = document.createElement('form');
                                            f.method = 'POST';
                                            f.action = '{{ route('admin.rfq.cancel', $rfq) }}';
                                            f.innerHTML = '<input type=&quot;hidden&quot; name=&quot;_token&quot; value=&quot;' + document.querySelector('meta[name=&quot;csrf-token&quot;]').getAttribute('content') + '&quot;>';
                                            document.body.appendChild(f);
                                            f.submit();
                                        }
                                    })">
                                <i class="fas fa-ban mr-1"></i> Cancelar RFQ
                            </button>
                        </div>
                    </div>
                @endif
            @endcan

            <a href="{{ route('admin.rfq.index') }}"
               class="btn btn-outline-secondary btn-block shadow-sm">
                <i class="fas fa-arrow-left mr-1"></i> Volver al Listado
            </a>

        </div>{{-- /col-lg-3 --}}

    </div>{{-- /row --}}
</div>

@include('admin.partials.confirm-action')
@stop

@section('css')
<style>
    .text-sm  { font-size: 0.875rem !important; }
    .text-xs  { font-size: 0.75rem  !important; }
    .show-exempt-check {
        transition: transform 0.15s ease;
    }
    .show-exempt-check:hover {
        transform: scale(1.2);
    }
</style>
@endsection

@section('js')
<script>
$(document).ready(function () {

    // Select2 en el selector de proveedor
    $('#supplier_id_show').select2({
        theme: 'bootstrap4',
        width: '100%',
        placeholder: '— Seleccione el proveedor ganador —'
    });

    // Confirmación antes de enviar el formulario de conversión
    $('#convertPoForm').on('submit', function (e) {
        e.preventDefault();
        const self = this;

        if (!$('#supplier_id_show').val()) {
            Swal.fire({
                icon: 'warning',
                title: 'Proveedor requerido',
                text: 'Debe seleccionar un proveedor antes de generar la Orden de Compra.'
            });
            return;
        }

        Swal.fire({
            icon: 'question',
            title: 'Generar Orden de Compra',
            html: '¿Desea convertir esta RFQ en una <strong>Orden de Compra</strong>?<br><small class="text-muted">Se abrirá el formulario para ingresar los precios finales.</small>',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-shopping-cart mr-1"></i> Sí, procesar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d'
        }).then(function (result) {
            if (result.isConfirmed) {
                self.submit();
            }
        });
    });

});
</script>
@endsection
