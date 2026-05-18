@extends('adminlte::page')

@section('title', 'Proveedor: ' . $supplier->name)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-building"></i> Proveedor: <strong>{{ $supplier->name }}</strong></h1>
        <div>
            <a href="{{ route('admin.suppliers.edit', $supplier) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Editar
            </a>
            <a href="{{ route('admin.suppliers.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
@stop

@section('content')
    @include('admin.partials.session-messages')

    <div class="row">
        <div class="col-12 col-md-6">
            <div class="card" style="border-left: 4px solid #f59e0b;">
                <div class="card-header" style="background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);">
                    <h3 class="card-title text-dark">
                        <i class="fas fa-id-card"></i> Identificación
                    </h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th width="35%">Nombre:</th>
                            <td><strong>{{ $supplier->name }}</strong></td>
                        </tr>
                        <tr>
                            <th>RIF:</th>
                            <td>{{ $supplier->tax_id ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Contacto:</th>
                            <td>{{ $supplier->contact_person ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Cédula Representante:</th>
                            <td>{{ $supplier->representative_cedula ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Estado:</th>
                            <td>
                                @if($supplier->is_active)
                                    <span class="badge badge-success">Activo</span>
                                @else
                                    <span class="badge badge-danger">Inactivo</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="card" style="border-left: 4px solid #3b82f6;">
                <div class="card-header" style="background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);">
                    <h3 class="card-title text-white">
                        <i class="fas fa-address-card"></i> Contacto
                    </h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th width="35%">Email:</th>
                            <td>
                                @if($supplier->email)
                                    <a href="mailto:{{ $supplier->email }}">{{ $supplier->email }}</a>
                                @else
                                    N/A
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Teléfono:</th>
                            <td>{{ $supplier->phone ?? 'N/A' }}
                                @if($supplier->phones)
                                    @foreach($supplier->phones as $extraPhone)
                                        <br>{{ $extraPhone }}
                                    @endforeach
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Dirección:</th>
                            <td>{{ $supplier->address ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Dirección Fiscal:</th>
                            <td>{{ $supplier->fiscal_address ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12 col-md-6">
            <div class="card" style="border-left: 4px solid #10b981;">
                <div class="card-header" style="background: linear-gradient(135deg, #10b981 0%, #34d399 100%);">
                    <h3 class="card-title text-white">
                        <i class="fas fa-shopping-cart"></i> Órdenes de Compra
                    </h3>
                </div>
                <div class="card-body">
                    @php $purchaseOrders = $supplier->purchaseOrders()->latest()->limit(10)->get(); @endphp
                    @if($purchaseOrders->count() > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($purchaseOrders as $po)
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <a href="{{ route('admin.purchaseOrders.show', $po) }}">
                                        {{ $po->code }}
                                    </a>
                                    <div>
                                        {!! $po->status_badge !!}
                                        <small class="text-muted ml-2">{{ $po->total }} {{ $po->currency }}</small>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                        @if($supplier->purchaseOrders()->count() > 10)
                            <a href="{{ route('admin.purchaseOrders.index', ['supplier_id' => $supplier->id]) }}" class="btn btn-sm btn-outline-success mt-2">
                                Ver todas las OC
                            </a>
                        @endif
                    @else
                        <p class="text-muted mb-0">Sin órdenes de compra registradas.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="card" style="border-left: 4px solid #8b5cf6;">
                <div class="card-header" style="background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%);">
                    <h3 class="card-title text-white">
                        <i class="fas fa-truck-loading"></i> Entradas de Inventario
                    </h3>
                </div>
                <div class="card-body">
                    @php $stockIns = $supplier->stockIns()->latest()->limit(10)->get(); @endphp
                    @if($stockIns->count() > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($stockIns as $entry)
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <a href="{{ route('admin.stock-in.show', $entry) }}">
                                        Entrada #{{ $entry->id }}
                                    </a>
                                    <small class="text-muted">{{ $entry->entry_date->format('d/m/Y') }}</small>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted mb-0">Sin entradas de inventario registradas.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop
