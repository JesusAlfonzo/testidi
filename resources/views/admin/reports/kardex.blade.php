@extends('adminlte::page')

@section('title', 'Kardex de Producto')

@section('content_header')
    <h1>Kardex de Producto: <strong>{{ $product->name }}</strong> ({{ $product->code }})</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-info card-outline">
                <div class="card-header">
                    <h3 class="card-title">Historial de Movimientos</h3>
                    <div class="card-tools">
                        {{-- 🟢 BOTONES DE EXPORTACIÓN --}}
                        <a href="{{ route('admin.reports.kardex.excel', $product->id) }}" class="btn btn-success btn-sm" title="Descargar Excel">
                            <i class="fas fa-file-excel"></i> Excel
                        </a>
                        <a href="{{ route('admin.reports.kardex.pdf', $product->id) }}" class="btn btn-danger btn-sm" target="_blank" title="Ver PDF">
                            <i class="fas fa-file-pdf"></i> PDF
                        </a>
                    </div>
                </div>
                <div class="card-body p-4">
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
                                    // Determinamos si es salida para colorear la fila
                                    $isOut = $movimiento['quantity'] < 0;
                                    $rowClass = $isOut ? 'table-warning' : 'table-success';
                                @endphp
                                <tr class="{{ $rowClass }}">
                                    {{-- Fecha formateada --}}
                                    <td>{{ \Carbon\Carbon::parse($movimiento['date'])->format('d/m/Y H:i') }}</td>
                                    
                                    {{-- Tipo de Movimiento --}}
                                    <td>
                                        <span class="badge badge-{{ $isOut ? 'danger' : 'success' }}">
                                            {{ $movimiento['type'] }}
                                        </span>
                                    </td>
                                    
                                    {{-- Referencia y Nota --}}
                                    <td>
                                        <strong>{{ $movimiento['reference'] }}</strong>
                                        <p class="text-muted mb-0 text-sm">{{ $movimiento['notes'] }}</p>
                                    </td>
                                    
                                    {{-- Costo --}}
                                    <td>${{ number_format($movimiento['unit_price'], 2) }}</td>
                                    
                                    {{-- Cantidad (mostramos valor absoluto para visualización limpia, el color indica signo) --}}
                                    <td>
                                        <span class="font-weight-bold">{{ abs($movimiento['quantity']) }}</span>
                                    </td>
                                    
                                    {{-- Saldo Acumulado --}}
                                    <td>
                                        <span class="badge badge-secondary badge-lg">{{ $movimiento['balance'] }}</span>
                                    </td>
                                    
                                    {{-- Usuario Responsable --}}
                                    <td>{{ $movimiento['user'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    <div class="float-right">
                        Stock actual según Kardex: <strong>{{ end($kardex)['balance'] ?? 0 }}</strong> | Stock en tabla `products`: <strong>{{ $product->stock }}</strong>
                    </div>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-default btn-sm">
                        <i class="fas fa-arrow-left"></i> Volver a Productos
                    </a>
                </div>
            </div>
        </div>
    </div>
@stop