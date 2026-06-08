@extends('adminlte::page')

@section('title', 'Proveedor | ' . $supplier->name)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="text-dark font-weight-bold" style="font-size: 1.75rem;">
                <i class="fas fa-truck text-primary mr-2"></i> Detalle del Proveedor
            </h1>
            <p class="text-muted mb-0">Ficha técnica y trazabilidad de operaciones del proveedor.</p>
        </div>
        <div class="d-flex">
            @can('proveedores_editar')
                <a href="{{ route('admin.suppliers.edit', $supplier) }}" class="btn btn-warning font-weight-bold px-3 mr-2" style="border-radius: 8px;">
                    <i class="fas fa-edit mr-1"></i> Editar
                </a>
            @endcan
            <a href="{{ route('admin.suppliers.index') }}" class="btn btn-secondary px-3" style="border-radius: 8px;">
                <i class="fas fa-arrow-left mr-1"></i> Volver al Listado
            </a>
        </div>
    </div>
@stop

@section('content')
    @include('admin.partials.session-messages')

    <div class="row">
        <div class="col-lg-10 mx-auto">
            {{-- Document Card --}}
            <div class="card p-5 bg-white shadow-sm mb-4" style="border-radius: 12px; border: 1px solid #e5e7eb;">
                
                {{-- Document Header --}}
                <div class="d-flex justify-content-between align-items-center border-bottom pb-4 mb-4">
                    <div>
                        <span class="text-uppercase text-xs font-weight-bold text-secondary tracking-wider" style="letter-spacing: 0.5px;">Ficha de Registro</span>
                        <h3 class="font-weight-bold text-dark mb-1">{{ $supplier->name }}</h3>
                        <p class="text-muted mb-0"><i class="fas fa-id-card text-muted mr-1"></i> RIF/ID Fiscal: <strong class="text-dark">{{ $supplier->tax_id ?? 'N/A' }}</strong></p>
                    </div>
                    <div class="text-right">
                        @if($supplier->is_active)
                            <span class="badge badge-success px-3 py-2" style="font-size: 0.85rem; border-radius: 20px;"><i class="fas fa-check-circle mr-1"></i> ACTIVO</span>
                        @else
                            <span class="badge badge-secondary px-3 py-2" style="font-size: 0.85rem; border-radius: 20px;"><i class="fas fa-times-circle mr-1"></i> INACTIVO</span>
                        @endif
                        <p class="text-xs text-muted mt-2 mb-0">ID Proveedor: #{{ $supplier->id }}</p>
                    </div>
                </div>

                {{-- Two-column layout for details --}}
                <div class="row mb-4">
                    {{-- Column 1: Identificación --}}
                    <div class="col-md-6 border-right">
                        <h6 class="font-weight-bold text-secondary text-uppercase tracking-wider mb-3" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                            <i class="fas fa-building text-info mr-2"></i> Datos de Identificación
                        </h6>
                        <table class="table table-borderless table-sm" style="font-size: 0.875rem;">
                            <tr>
                                <td class="text-muted pl-0" style="width: 40%;">Razón Social:</td>
                                <td class="font-weight-bold text-dark">{{ $supplier->name }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted pl-0">RIF / ID Fiscal:</td>
                                <td class="text-dark">{{ $supplier->tax_id ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted pl-0">Persona de Contacto:</td>
                                <td class="text-dark font-weight-bold">{{ $supplier->contact_person ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted pl-0">Cédula Representante:</td>
                                <td class="text-dark">{{ $supplier->representative_cedula ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted pl-0">Registrado Por:</td>
                                <td class="text-dark">{{ $supplier->user->name ?? 'Sistema' }}</td>
                            </tr>
                        </table>
                    </div>

                    {{-- Column 2: Contacto & Direcciones --}}
                    <div class="col-md-6 pl-md-4">
                        <h6 class="font-weight-bold text-secondary text-uppercase tracking-wider mb-3" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                            <i class="fas fa-address-book text-info mr-2"></i> Contacto y Direcciones
                        </h6>
                        <table class="table table-borderless table-sm" style="font-size: 0.875rem;">
                            <tr>
                                <td class="text-muted pl-0" style="width: 35%;">Email:</td>
                                <td>
                                    @if($supplier->email)
                                        <a href="mailto:{{ $supplier->email }}" class="text-primary font-weight-bold"><i class="fas fa-envelope mr-1"></i> {{ $supplier->email }}</a>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted pl-0">Teléfono:</td>
                                <td class="text-dark font-weight-bold">
                                    <i class="fas fa-phone mr-1 text-muted"></i> {{ $supplier->phone ?? 'N/A' }}
                                    @if($supplier->phones)
                                        @foreach($supplier->phones as $extraPhone)
                                            <br><i class="fas fa-phone mr-1 text-muted"></i> {{ $extraPhone }}
                                        @endforeach
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted pl-0">Dirección Física:</td>
                                <td class="text-dark" style="line-height: 1.4;">{{ $supplier->address ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted pl-0">Dirección Fiscal:</td>
                                <td class="text-dark" style="line-height: 1.4;">{{ $supplier->fiscal_address ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <hr style="border-top: 1px solid #e5e7eb; margin: 2rem 0;">

                {{-- History Section --}}
                <div class="row">
                    {{-- Left: Purchase Orders --}}
                    <div class="col-md-6 border-right pr-md-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="font-weight-bold text-secondary text-uppercase tracking-wider mb-0" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                                <i class="fas fa-shopping-cart text-success mr-2"></i> Órdenes de Compra (Últimas 10)
                            </h6>
                        </div>
                        @php $purchaseOrders = $supplier->purchaseOrders()->latest()->limit(10)->get(); @endphp
                        @if($purchaseOrders->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover table-sm table-valign-middle" style="font-size: 0.85rem;">
                                    <thead>
                                        <tr class="bg-light">
                                            <th class="border-0 rounded-left py-2">Código</th>
                                            <th class="border-0 py-2">Total</th>
                                            <th class="border-0 rounded-right py-2 text-right">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($purchaseOrders as $po)
                                            <tr>
                                                <td class="py-2"><a href="{{ route('admin.purchaseOrders.show', $po) }}" class="font-weight-bold text-primary">{{ $po->code }}</a></td>
                                                <td class="py-2">{{ number_format($po->total, 2) }} {{ $po->currency }}</td>
                                                <td class="py-2 text-right">{!! $po->status_badge !!}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @if($supplier->purchaseOrders()->count() > 10)
                                <div class="text-right mt-2">
                                    <a href="{{ route('admin.purchaseOrders.index', ['supplier_id' => $supplier->id]) }}" class="btn btn-xs btn-outline-success font-weight-bold" style="border-radius: 4px;">
                                        Ver todas las OC <i class="fas fa-arrow-right ml-1"></i>
                                    </a>
                                </div>
                            @endif
                        @else
                            <div class="text-center py-4 bg-light rounded" style="border: 1px dashed #e5e7eb; border-radius: 8px;">
                                <p class="text-muted mb-0"><i class="fas fa-info-circle mr-1"></i> Sin órdenes de compra registradas.</p>
                            </div>
                        @endif
                    </div>

                    {{-- Right: Stock Ins --}}
                    <div class="col-md-6 pl-md-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="font-weight-bold text-secondary text-uppercase tracking-wider mb-0" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                                <i class="fas fa-truck-loading text-purple mr-2"></i> Recepciones / Entradas (Últimas 10)
                            </h6>
                        </div>
                        @php $stockIns = $supplier->stockIns()->latest()->limit(10)->get(); @endphp
                        @if($stockIns->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover table-sm table-valign-middle" style="font-size: 0.85rem;">
                                    <thead>
                                        <tr class="bg-light">
                                            <th class="border-0 rounded-left py-2">ID Entrada</th>
                                            <th class="border-0 py-2">Factura</th>
                                            <th class="border-0 rounded-right py-2 text-right">Fecha Entrada</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($stockIns as $entry)
                                            <tr>
                                                <td class="py-2"><a href="{{ route('admin.stock-in.show', $entry) }}" class="font-weight-bold text-purple">#{{ $entry->id }}</a></td>
                                                <td class="py-2">{{ $entry->invoice_number ?? 'N/A' }}</td>
                                                <td class="py-2 text-right text-muted">{{ $entry->entry_date->format('d/m/Y') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4 bg-light rounded" style="border: 1px dashed #e5e7eb; border-radius: 8px;">
                                <p class="text-muted mb-0"><i class="fas fa-info-circle mr-1"></i> Sin entradas de inventario registradas.</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Audit footer --}}
                <div class="row mt-5 pt-3 border-top" style="font-size: 0.75rem; color: #9ca3af;">
                    <div class="col-6">
                        <span>Creado por: <strong>{{ $supplier->user->name ?? 'N/A' }}</strong> el {{ $supplier->created_at->format('d/m/Y H:i:s') }}</span>
                    </div>
                    <div class="col-6 text-right">
                        <span>Última actualización: {{ $supplier->updated_at->format('d/m/Y H:i:s') }}</span>
                    </div>
                </div>

            </div>
        </div>
    </div>
@stop
