@extends('adminlte::page')

@section('title', 'Ver Kit')

@section('content_header')
    <h1 class="m-0 text-dark">Kit: {{ $kit->name }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12 col-md-6">
            <div class="card" style="border-left: 4px solid #8b5cf6;">
                <div class="card-header" style="background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%);">
                    <h3 class="card-title text-white">
                        <i class="fas fa-info-circle"></i> Detalles del Kit
                    </h3>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">ID:</dt>
                        <dd class="col-sm-8">{{ $kit->id }}</dd>

                        <dt class="col-sm-4">Nombre:</dt>
                        <dd class="col-sm-8">{{ $kit->name }}</dd>
                        
                        <dt class="col-sm-4">Precio:</dt>
                        <dd class="col-sm-8">${{ number_format($kit->unit_price, 2) }}</dd>

                        <dt class="col-sm-4">Kits Disponibles:</dt>
                        <dd class="col-sm-8">
                            @if($kit->available_stock > 5)
                                <span class="badge badge-success py-1 px-2" style="font-size: 0.9rem;"><i class="fas fa-check-circle"></i> {{ $kit->available_stock }} kits</span>
                            @elseif($kit->available_stock > 0)
                                <span class="badge badge-warning text-dark py-1 px-2" style="font-size: 0.9rem;"><i class="fas fa-exclamation-triangle"></i> {{ $kit->available_stock }} kits</span>
                            @else
                                <span class="badge badge-danger py-1 px-2" style="font-size: 0.9rem;"><i class="fas fa-times-circle"></i> Sin stock</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4">Descripción:</dt>
                        <dd class="col-sm-8">{{ $kit->description ?? 'N/A' }}</dd>
                        
                        <dt class="col-sm-4">Estado:</dt>
                        <dd class="col-sm-8">
                            @if($kit->is_active)
                                <span class="badge badge-success">Activo</span>
                            @else
                                <span class="badge badge-danger">Inactivo</span>
                            @endif
                        </dd>
                    </dl>
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.kits.edit', $kit) }}" class="btn btn-warning text-dark"><i class="fas fa-edit"></i> Editar</a>
                    <a href="{{ route('admin.kits.index') }}" class="btn btn-default"><i class="fas fa-arrow-left"></i> Volver al Listado</a>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="card" style="border-left: 4px solid #06b6d4;">
                <div class="card-header" style="background: linear-gradient(135deg, #06b6d4 0%, #22d3ee 100%);">
                    <h3 class="card-title text-white">
                        <i class="fas fa-box-open"></i> Componentes Requeridos
                    </h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead class="bg-info">
                            <tr>
                                <th>Producto</th>
                                <th class="text-center">Stock Individual</th>
                                <th class="text-center">Cant. Requerida</th>
                                <th class="text-right">Kits Formables</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($kit->components as $component)
                                @php
                                    $stock = $component->stock ?? 0;
                                    $required = $component->pivot->quantity_required;
                                    $formable = $required > 0 ? (int) floor($stock / $required) : 0;
                                    $isLimiting = $formable === $kit->available_stock && $kit->available_stock === 0;
                                @endphp
                                <tr class="{{ $isLimiting ? 'table-danger' : '' }}">
                                    <td>
                                        <strong>{{ $component->name }}</strong>
                                        @if($isLimiting)
                                            <span class="badge badge-danger ml-2"><i class="fas fa-exclamation-triangle"></i> Limitante</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $stock >= $required ? 'badge-success' : 'badge-danger' }} py-1 px-2">
                                            {{ $stock }}
                                        </span>
                                    </td>
                                    <td class="text-center">{{ $required }}</td>
                                    <td class="text-right font-weight-bold {{ $formable == 0 ? 'text-danger' : 'text-success' }}">
                                        {{ $formable }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">Este Kit no tiene componentes definidos.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop