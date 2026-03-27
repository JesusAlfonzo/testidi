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
                    <table class="table table-sm table-striped mb-0">
                        <thead class="bg-info">
                            <tr>
                                <th>Producto</th>
                                <th class="text-right">Cant. Requerida</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($kit->components as $component)
                                <tr>
                                    <td>{{ $component->name }}</td>
                                    <td class="text-right">{{ $component->pivot->quantity_required }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center">Este Kit no tiene componentes definidos.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop