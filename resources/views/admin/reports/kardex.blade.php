@extends('adminlte::page')

@section('title', 'Kardex de Producto')

@section('content_header')
    <h1>Kardex de Producto: {{ $product->name }} ({{ $product->code }})</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-info card-outline">
                <div class="card-header">
                    <h3 class="card-title">Historial de Movimientos</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th style="width: 15%">Fecha</th>
                                <th style="width: 10%">Tipo</th>
                                <th>Referencia</th>
                                <th style="width: 15%">Costo Unitario</th>
                                <th style="width: 10%">Cantidad</th>
                                <th style="width: 10%">Saldo</th>
                                <th>Procesado Por</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($kardex as $movimiento)
                                @php
                                    $isOut = $movimiento['quantity'] < 0;
                                    $rowClass = $isOut ? 'table-warning' : 'table-success';
                                @endphp
                                <tr class="{{ $rowClass }}">
                                    <td>{{ \Carbon\Carbon::parse($movimiento['date'])->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <span class="badge badge-{{ $isOut ? 'danger' : 'success' }}">
                                            {{ $movimiento['type'] }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong>{{ $movimiento['reference'] }}</strong>
                                        <p class="text-muted mb-0 text-sm">{{ $movimiento['notes'] }}</p>
                                    </td>
                                    <td>{{ number_format($movimiento['unit_price'], 2) }}</td>
                                    <td>
                                        <span class="font-weight-bold">{{ abs($movimiento['quantity']) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary badge-lg">{{ $movimiento['balance'] }}</span>
                                    </td>
                                    <td>{{ $movimiento['user'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    Stock actual según Kardex: <strong>{{ end($kardex)['balance'] ?? 0 }}</strong> | Stock en tabla `products`: <strong>{{ $product->stock }}</strong>
                </div>
            </div>
        </div>
    </div>
@stop
