@extends('adminlte::page')

@section('title', 'Ver Kit')

@section('content_header')
    <h1 class="m-0 text-dark">Kit: {{ $kit->name }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-6">
            <x-adminlte-card title="Detalles del Kit" icon="fas fa-info-circle" theme="primary" collapsible>
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
                
                <x-slot name="footerSlot">
                    <a href="{{ route('admin.kits.edit', $kit) }}" class="btn btn-warning"><i class="fas fa-edit"></i> Editar</a>
                    <a href="{{ route('admin.kits.index') }}" class="btn btn-default"><i class="fas fa-arrow-left"></i> Volver al Listado</a>
                </x-slot>
            </x-adminlte-card>
        </div>

        <div class="col-md-6">
            <x-adminlte-card title="Componentes Requeridos" icon="fas fa-box-open" theme="secondary" collapsible>
                <table class="table table-sm table-striped">
                    <thead>
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
            </x-adminlte-card>
        </div>
    </div>
@stop