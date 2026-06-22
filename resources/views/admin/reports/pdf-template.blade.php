<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $report_title }}</title>
    <style>
        @page {
            margin: 1.5cm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #333;
            line-height: 1.4;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .header-text {
            text-align: left;
            vertical-align: middle;
        }
        .header-meta {
            text-align: right;
            vertical-align: middle;
            font-size: 9px;
            color: #666;
        }
        .institution-name {
            font-size: 13px;
            font-weight: bold;
            color: #0d6efd;
            margin: 0;
            text-transform: uppercase;
        }
        .system-title {
            font-size: 10px;
            color: #555;
            margin: 3px 0 0 0;
            font-weight: normal;
        }
        .report-title-section {
            margin-bottom: 15px;
            background-color: #f8f9fa;
            padding: 8px 12px;
            border-left: 4px solid #007bff;
        }
        .report-title {
            font-size: 12px;
            font-weight: bold;
            color: #333;
            margin: 0;
            text-transform: uppercase;
        }
        .filter-badges {
            font-size: 9px;
            color: #555;
            margin-top: 4px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .data-table th {
            background-color: #eef2f7;
            color: #333;
            font-weight: bold;
            text-align: left;
            padding: 6px 5px;
            border-bottom: 1.5px solid #bdc3c7;
            text-transform: uppercase;
            font-size: 8px;
        }
        .data-table td {
            padding: 6px 5px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 9px;
            vertical-align: middle;
        }
        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .data-table tr {
            page-break-inside: avoid;
        }
        .text-success { color: #2e7d32; font-weight: bold; }
        .text-danger { color: #c62828; font-weight: bold; }
        .text-muted { color: #718096; }
        .badge {
            padding: 2px 4px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            display: inline-block;
        }
        .badge-success { background-color: #e8f5e9; color: #2e7d32; }
        .badge-danger { background-color: #ffebee; color: #c62828; }
        .badge-warning { background-color: #fff8e1; color: #f57f17; }
        .badge-info { background-color: #e0f7fa; color: #006064; }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 20px;
            text-align: center;
            font-size: 8px;
            color: #aaa;
            border-top: 1px solid #eee;
            padding-top: 5px;
        }
    </style>
</head>
<body>

    {{-- MEMBRETE INSTITUCIONAL --}}
    <table class="header-table">
        <tr>
            <td class="header-text">
                <h1 class="institution-name">Instituto de Inmunología "Dr. Nicolás E. Bianco Colmenares"</h1>
                <h2 class="system-title">SGCI-IDI — Sistema de Gestión y Control de Inventarios</h2>
            </td>
            <td class="header-meta">
                <strong>Fecha Emisión:</strong> {{ $generated_at }}<br>
                <strong>Emitido por:</strong> {{ auth()->user()->name ?? 'Sistema' }}
            </td>
        </tr>
    </table>

    {{-- DETALLES DEL REPORTE --}}
    <div class="report-title-section">
        <h3 class="report-title">{{ $report_title }}</h3>
        <div class="filter-badges">
            <strong>Filtros aplicados:</strong>
            @if(!empty($filters['fecha_inicio']) || !empty($filters['fecha_fin']))
                <span>Rango: [{{ $filters['fecha_inicio'] ?? 'Inicio' }} al {{ $filters['fecha_fin'] ?? 'Fin' }}]</span> |
            @endif
            @if(!empty($filters['product_id']))
                <span>Producto: ID {{ $filters['product_id'] }}</span> |
            @endif
            @if(!empty($filters['batch_number']))
                <span>Lote: "{{ $filters['batch_number'] }}"</span> |
            @endif
            @if(!empty($filters['user_id']))
                <span>Usuario: ID {{ $filters['user_id'] }}</span> |
            @endif
            <span>Total: {{ $data->count() }} registros</span>
        </div>
    </div>

    {{-- TABLA DINÁMICA DE DATOS --}}
    <table class="data-table">
        <thead>
            @if($report_type == 'inventario')
                <tr>
                    <th style="width: 12%">SKU / Código</th>
                    <th style="width: 25%">Producto</th>
                    <th style="width: 15%">Categoría</th>
                    <th style="width: 15%">Ubicación</th>
                    <th style="width: 8%">Stock</th>
                    <th style="width: 8%">U. Medida</th>
                    <th style="width: 8%">Min. Stock</th>
                    <th style="width: 9%">Costo Unit.</th>
                </tr>
            @elseif($report_type == 'entradas')
                <tr>
                    <th style="width: 10%">Fecha</th>
                    <th style="width: 18%">Documento</th>
                    <th style="width: 20%">Producto</th>
                    <th style="width: 15%">Lote</th>
                    <th style="width: 10%">Cant. Recibida</th>
                    <th style="width: 9%">Costo Unit.</th>
                    <th style="width: 9%">Total</th>
                    <th style="width: 9%">Proveedor</th>
                </tr>
            @elseif($report_type == 'salidas')
                <tr>
                    <th style="width: 12%">Fecha</th>
                    <th style="width: 12%">Nro Solicitud</th>
                    <th style="width: 25%">Producto/Kit</th>
                    <th style="width: 10%">Cantidad</th>
                    <th style="width: 23%">Justificación</th>
                    <th style="width: 9%">Solicitante</th>
                    <th style="width: 9%">Autorizador</th>
                </tr>
            @elseif($report_type == 'fraccionamientos')
                <tr>
                    <th style="width: 15%">Fecha / Hora</th>
                    <th style="width: 22%">Producto Afectado</th>
                    <th style="width: 12%">Acción</th>
                    <th style="width: 10%">Cantidad</th>
                    <th style="width: 10%">Stock Final</th>
                    <th style="width: 11%">Usuario</th>
                    <th style="width: 20%">Detalle / Nota</th>
                </tr>
            @endif
        </thead>
        <tbody>
            @if($data->isEmpty())
                <tr>
                    <td colspan="10" style="text-align: center; padding: 20px; color: #888;">
                        No se encontraron registros que coincidan con los criterios de búsqueda.
                    </td>
                </tr>
            @else
                @if($report_type == 'inventario')
                    @foreach($data as $product)
                        <tr>
                            <td style="font-weight: bold; color: #555;">{{ $product->code ?? 'N/A' }}</td>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->category->name ?? 'N/A' }}</td>
                            <td>{{ $product->location->name ?? 'N/A' }}</td>
                            <td style="font-weight: bold;">
                                <span class="badge {{ $product->stock <= $product->min_stock ? 'badge-danger' : 'badge-success' }}">
                                    {{ $product->stock }}
                                </span>
                            </td>
                            <td>{{ $product->unit->name ?? 'N/A' }}</td>
                            <td>{{ $product->min_stock }}</td>
                            <td>${{ number_format($product->cost, 2) }}</td>
                        </tr>
                    @endforeach
                @elseif($report_type == 'entradas')
                    @foreach($data as $item)
                        <tr>
                            <td>{{ $item->stockIn->entry_date ? $item->stockIn->entry_date->format('d/m/Y') : $item->created_at->format('d/m/Y') }}</td>
                            <td style="font-weight: bold; color: #555;">{{ $item->stockIn->document_type }}: {{ $item->stockIn->document_number }}</td>
                            <td>{{ $item->product->name }}</td>
                            <td>
                                <strong>Lote:</strong> {{ $item->batch_number ?? 'N/A' }}
                                @if($item->expiration_date)
                                    <br><small style="color: #c62828;">Vence: {{ $item->expiration_date->format('d/m/Y') }}</small>
                                @endif
                            </td>
                            <td style="font-weight: bold;" class="text-success">+{{ $item->quantity }}</td>
                            <td>${{ number_format($item->unit_cost, 2) }}</td>
                            <td style="font-weight: bold;">${{ number_format($item->quantity * $item->unit_cost, 2) }}</td>
                            <td>{{ $item->stockIn->supplier->name ?? 'Ajuste interno' }}</td>
                        </tr>
                    @endforeach
                @elseif($report_type == 'salidas')
                    @foreach($data as $item)
                        <tr>
                            <td>{{ $item->request->processed_at ? $item->request->processed_at->format('d/m/Y H:i') : $item->created_at->format('d/m/Y H:i') }}</td>
                            <td style="font-weight: bold; color: #555;">REQ-{{ $item->request->id }}</td>
                            <td>
                                @if($item->item_type == 'kit' && $item->kit)
                                    <strong>Kit: {{ $item->kit->name }}</strong><br>
                                    <small style="color: #666;">Comp: {{ $item->product->name ?? 'N/A' }}</small>
                                @else
                                    {{ $item->product->name }}
                                @endif
                            </td>
                            <td style="font-weight: bold;" class="text-danger">-{{ $item->quantity_requested }}</td>
                            <td style="color: #555;">{{ Str::limit($item->request->justification, 60) }}</td>
                            <td>{{ $item->request->requester->name ?? 'N/A' }}</td>
                            <td>{{ $item->request->approver->name ?? 'N/A' }}</td>
                        </tr>
                    @endforeach
                @elseif($report_type == 'fraccionamientos')
                    @foreach($data as $activity)
                        @php
                            $props = $activity->properties;
                            $qty = $props['quantity'] ?? 0;
                            $type = $props['type'] ?? 'out';
                        @endphp
                        <tr>
                            <td>{{ $activity->created_at->format('d/m/Y H:i:s') }}</td>
                            <td style="font-weight: bold;">{{ $activity->subject->name ?? 'Producto Eliminado' }}</td>
                            <td>
                                <span class="badge {{ $type == 'out' ? 'badge-warning' : 'badge-success' }}">
                                    {{ $type == 'out' ? 'Origen (Caja)' : 'Destino (Unidad)' }}
                                </span>
                            </td>
                            <td style="font-weight: bold;" class="{{ $type == 'in' ? 'text-success' : 'text-danger' }}">
                                {{ $type == 'in' ? '+' : '-' }}{{ $qty }}
                            </td>
                            <td style="font-weight: bold; color: #555;">{{ $props['stock_after'] ?? 'N/A' }}</td>
                            <td>{{ $activity->causer->name ?? 'Sistema' }}</td>
                            <td style="color: #555;">{{ $activity->description }}</td>
                        </tr>
                    @endforeach
                @endif
            @endif
        </tbody>
        <tfoot style="background-color: #f8f9fa; font-weight: bold;">
            @if($report_type == 'inventario')
                <tr style="border-top: 1.5px solid #bdc3c7;">
                    <td colspan="4" style="text-align: right; font-size: 8px; text-transform: uppercase; color: #555; padding: 6px 5px;">Total Registros: {{ $totals['count'] }} | Totales:</td>
                    <td style="font-weight: bold; padding: 6px 5px;">{{ $totals['sum_quantity'] }}</td>
                    <td colspan="2" style="padding: 6px 5px;"></td>
                    <td style="font-weight: bold; color: #0d6efd; padding: 6px 5px;">${{ number_format($totals['sum_amount'], 2) }}</td>
                </tr>
            @elseif($report_type == 'entradas')
                <tr style="border-top: 1.5px solid #bdc3c7;">
                    <td colspan="4" style="text-align: right; font-size: 8px; text-transform: uppercase; color: #555; padding: 6px 5px;">Total Registros: {{ $totals['count'] }} | Totales:</td>
                    <td style="font-weight: bold; color: #2e7d32; padding: 6px 5px;">+{{ $totals['sum_quantity'] }}</td>
                    <td style="padding: 6px 5px;"></td>
                    <td style="font-weight: bold; padding: 6px 5px;">${{ number_format($totals['sum_amount'], 2) }}</td>
                    <td style="padding: 6px 5px;"></td>
                </tr>
            @elseif($report_type == 'salidas')
                <tr style="border-top: 1.5px solid #bdc3c7;">
                    <td colspan="3" style="text-align: right; font-size: 8px; text-transform: uppercase; color: #555; padding: 6px 5px;">Total Registros: {{ $totals['count'] }} | Totales:</td>
                    <td style="font-weight: bold; color: #c62828; padding: 6px 5px;">-{{ $totals['sum_quantity'] }}</td>
                    <td colspan="3" style="padding: 6px 5px;"></td>
                </tr>
            @elseif($report_type == 'fraccionamientos')
                <tr style="border-top: 1.5px solid #bdc3c7;">
                    <td colspan="3" style="text-align: right; font-size: 8px; text-transform: uppercase; color: #555; padding: 6px 5px;">Total Registros: {{ $totals['count'] }} | Totales:</td>
                    <td style="font-weight: bold; padding: 6px 5px;">{{ $totals['sum_quantity'] }}</td>
                    <td colspan="3" style="padding: 6px 5px;"></td>
                </tr>
            @endif
        </tfoot>
    </table>

    {{-- FOOTER DE PÁGINAS --}}
    <div class="footer">
        Documento de Control de Inventario Interno — SGCI-IDI — Página 1 de 1
    </div>

</body>
</html>
