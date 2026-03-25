<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kardex - {{ $product->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            padding: 20px;
            color: #333;
        }
        .header-container {
            width: 100%;
            margin-bottom: 20px;
            border-bottom: 2px solid #1a4a7a;
            padding-bottom: 15px;
        }
        .header-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .logo-section {
            width: 180px;
        }
        .logo-section img {
            width: 150px;
            height: auto;
        }
        .company-info {
            text-align: right;
            flex: 1;
            padding-left: 20px;
        }
        .company-name {
            font-size: 16px;
            font-weight: bold;
            color: #1a4a7a;
            margin-bottom: 3px;
        }
        .company-rif {
            font-size: 11px;
            color: #666;
        }
        .document-info {
            margin-top: 15px;
            text-align: right;
        }
        .document-title {
            font-size: 18px;
            font-weight: bold;
            color: #1a4a7a;
            margin-bottom: 5px;
        }
        .product-info-card {
            background: #f0f5fa;
            border: 1px solid #1a4a7a;
            padding: 15px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
        }
        .product-info-left {
            flex: 1;
        }
        .product-info-right {
            text-align: right;
        }
        .product-name {
            font-size: 14px;
            font-weight: bold;
            color: #1a4a7a;
            margin-bottom: 5px;
        }
        .product-detail {
            margin-bottom: 3px;
            color: #555;
        }
        .product-label {
            font-weight: bold;
            color: #333;
        }
        .stock-badge {
            display: inline-block;
            padding: 8px 20px;
            background: #1a4a7a;
            color: white;
            font-size: 16px;
            font-weight: bold;
            border-radius: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
        }
        th {
            background: #1a4a7a;
            color: white;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .type-in {
            background: #d4edda;
            color: #155724;
            padding: 3px 8px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 10px;
        }
        .type-out {
            background: #f8d7da;
            color: #721c24;
            padding: 3px 8px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 10px;
        }
        .type-adjust {
            background: #fff3cd;
            color: #856404;
            padding: 3px 8px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 10px;
        }
        .quantity-in {
            color: #155724;
            font-weight: bold;
        }
        .quantity-out {
            color: #721c24;
            font-weight: bold;
        }
        .balance-cell {
            font-weight: bold;
            background: #f8f9fa;
        }
        .summary-section {
            display: flex;
            justify-content: flex-end;
            gap: 20px;
            margin-top: 20px;
        }
        .summary-box {
            border: 2px solid #1a4a7a;
            padding: 15px 25px;
            background: #f0f5fa;
            text-align: center;
        }
        .summary-label {
            font-size: 10px;
            text-transform: uppercase;
            color: #1a4a7a;
            margin-bottom: 5px;
        }
        .summary-value {
            font-size: 18px;
            font-weight: bold;
            color: #1a4a7a;
        }
        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            font-size: 9px;
            color: #888;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header-container">
        <div class="header-row">
            <div class="logo-section">
                <img src="{{ public_path('images/logo-iac.png') }}" alt="Logo IAC">
            </div>
            <div class="company-info">
                <div class="company-name">Inmunologia Asociacion Civil</div>
                <div class="company-rif">RIF: J-30710739-1</div>
                <div class="document-info">
                    <div class="document-title">KARDEX DE INVENTARIO</div>
                    <div class="document-number">Producto: {{ $product->code }}</div>
                    <div class="document-date">Generado: {{ now()->format('d/m/Y') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="product-info-card">
        <div class="product-info-left">
            <div class="product-name">{{ $product->name }}</div>
            <div class="product-detail"><span class="product-label">Código:</span> {{ $product->code }}</div>
            <div class="product-detail"><span class="product-label">Categoría:</span> {{ $product->category->name ?? 'N/A' }}</div>
            <div class="product-detail"><span class="product-label">Marca:</span> {{ $product->brand->name ?? 'N/A' }}</div>
            <div class="product-detail"><span class="product-label">Unidad:</span> {{ $product->unit->abbreviation ?? 'und' }}</div>
            <div class="product-detail"><span class="product-label">Costo Actual:</span> {{ number_format($product->cost, 2) }}</div>
        </div>
        <div class="product-info-right">
            <div class="summary-label">Stock Actual</div>
            <div class="stock-badge">{{ $product->stock }} {{ $product->unit->abbreviation ?? '' }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 12%; text-align: center;">Fecha</th>
                <th style="width: 10%; text-align: center;">Tipo</th>
                <th style="width: 15%;">Referencia</th>
                <th style="width: 28%;">Detalle</th>
                <th style="width: 10%; text-align: right;">Entrada</th>
                <th style="width: 10%; text-align: right;">Salida</th>
                <th style="width: 10%; text-align: right;">Saldo</th>
                <th style="width: 5%; text-align: center;">Und.</th>
            </tr>
        </thead>
        <tbody>
            @forelse($kardex as $mov)
                <tr>
                    <td class="text-center">{{ \Carbon\Carbon::parse($mov['date'])->format('d/m/Y') }}</td>
                    <td class="text-center">
                        @php
                            $typeLabel = match($mov['type']) {
                                'in' => 'Entrada',
                                'out' => 'Salida',
                                'Entrada' => 'Entrada',
                                'Salida' => 'Salida',
                                default => ucfirst($mov['type'])
                            };
                            $typeClass = in_array($mov['type'], ['in', 'Entrada']) ? 'type-in' : (in_array($mov['type'], ['out', 'Salida']) ? 'type-out' : 'type-adjust');
                        @endphp
                        <span class="{{ $typeClass }}">{{ $typeLabel }}</span>
                    </td>
                    <td>{{ $mov['reference'] ?? '-' }}</td>
                    <td>{{ $mov['notes'] ?? '-' }}</td>
                    <td class="text-right quantity-in">
                        {{ isset($mov['quantity']) && $mov['quantity'] > 0 ? '+' . $mov['quantity'] : '' }}
                    </td>
                    <td class="text-right quantity-out">
                        {{ isset($mov['quantity']) && $mov['quantity'] < 0 ? $mov['quantity'] : '' }}
                    </td>
                    <td class="text-right balance-cell">{{ $mov['balance'] ?? 0 }}</td>
                    <td class="text-center">{{ $product->unit->abbreviation ?? 'und' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">No hay movimientos registrados</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @php
        $totalEntradas = collect($kardex)->where('quantity', '>', 0)->sum('quantity');
        $totalSalidas = collect($kardex)->where('quantity', '<', 0)->sum('quantity');
    @endphp

    <div class="summary-section">
        <div class="summary-box">
            <div class="summary-label">Total Entradas</div>
            <div class="summary-value" style="color: #155724;">+{{ abs($totalEntradas) }}</div>
        </div>
        <div class="summary-box">
            <div class="summary-label">Total Salidas</div>
            <div class="summary-value" style="color: #721c24;">{{ abs($totalSalidas) }}</div>
        </div>
        <div class="summary-box">
            <div class="summary-label">Stock Final</div>
            <div class="summary-value">{{ end($kardex)['balance'] ?? $product->stock }}</div>
        </div>
    </div>

    <div class="footer">
        <p>Documento generado el {{ now()->format('d/m/Y H:i') }} | Sistema de Gestión de Inventario - IAC</p>
    </div>
</body>
</html>
