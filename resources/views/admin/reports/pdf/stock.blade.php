<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Stock</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; padding: 20px; color: #333; }
        .header-container { width: 100%; margin-bottom: 20px; border-bottom: 2px solid #1a4a7a; padding-bottom: 15px; }
        .header-row { display: flex; justify-content: space-between; align-items: flex-start; }
        .logo-section { width: 180px; }
        .logo-section img { width: 150px; height: auto; }
        .company-info { text-align: right; flex: 1; padding-left: 20px; }
        .company-name { font-size: 16px; font-weight: bold; color: #1a4a7a; margin-bottom: 3px; }
        .company-rif { font-size: 11px; color: #666; }
        .document-info { margin-top: 15px; text-align: right; }
        .document-title { font-size: 18px; font-weight: bold; color: #1a4a7a; margin-bottom: 5px; }
        .document-date { font-size: 11px; color: #666; }
        
        .summary-row { display: flex; gap: 15px; margin-bottom: 15px; }
        .summary-box { flex: 1; border: 1px solid #ddd; padding: 12px; background: #fafafa; text-align: center; }
        .summary-label { font-size: 10px; text-transform: uppercase; color: #666; margin-bottom: 5px; }
        .summary-value { font-size: 24px; font-weight: bold; color: #1a4a7a; }
        .summary-value.warning { color: #dc3545; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #333; padding: 8px; text-align: left; }
        th { background: #1a4a7a; color: white; font-weight: bold; font-size: 10px; text-transform: uppercase; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .stock-optimal { background: #d4edda; color: #155724; padding: 3px 10px; border-radius: 3px; font-weight: bold; font-size: 10px; }
        .stock-low { background: #f8d7da; color: #721c24; padding: 3px 10px; border-radius: 3px; font-weight: bold; font-size: 10px; }
        .stock-empty { background: #dc3545; color: white; padding: 3px 10px; border-radius: 3px; font-weight: bold; font-size: 10px; }
        .footer { margin-top: 20px; padding-top: 15px; border-top: 1px solid #ddd; font-size: 9px; color: #888; text-align: center; }
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
                    <div class="document-title">REPORTE DE INVENTARIO</div>
                    <div class="document-date">Generado: {{ now()->format('d/m/Y') }}</div>
                </div>
            </div>
        </div>
    </div>

    @php
        $totalProductos = $products->count();
        $productosStockBajo = $products->filter(fn($p) => $p->stock <= $p->min_stock)->count();
        $productosSinStock = $products->filter(fn($p) => $p->stock == 0)->count();
        $stockTotal = $products->sum('stock');
    @endphp

    <table style="width: 100%; margin-bottom: 15px; border: none;">
        <tr>
            <td style="width: 50%; padding: 5px;">
                <table style="width: 100%; border: 2px solid #1a4a7a; background: #f0f5fa;">
                    <tr>
                        <td style="padding: 10px; text-align: center;">
                            <div style="font-size: 9px; text-transform: uppercase; color: #1a4a7a;">Total Productos</div>
                            <div style="font-size: 22px; font-weight: bold; color: #1a4a7a;">{{ $totalProductos }}</div>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 50%; padding: 5px;">
                <table style="width: 100%; border: 2px solid #1a4a7a; background: #f0f5fa;">
                    <tr>
                        <td style="padding: 10px; text-align: center;">
                            <div style="font-size: 9px; text-transform: uppercase; color: #1a4a7a;">Total Unidades</div>
                            <div style="font-size: 22px; font-weight: bold; color: #1a4a7a;">{{ number_format($stockTotal) }}</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="width: 50%; padding: 5px;">
                <table style="width: 100%; border: 2px solid #dc3545; background: #fff5f5;">
                    <tr>
                        <td style="padding: 10px; text-align: center;">
                            <div style="font-size: 9px; text-transform: uppercase; color: #dc3545;">Stock Bajo Mínimo</div>
                            <div style="font-size: 22px; font-weight: bold; color: #dc3545;">{{ $productosStockBajo }}</div>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 50%; padding: 5px;">
                <table style="width: 100%; border: 2px solid #dc3545; background: #fff5f5;">
                    <tr>
                        <td style="padding: 10px; text-align: center;">
                            <div style="font-size: 9px; text-transform: uppercase; color: #dc3545;">Sin Stock</div>
                            <div style="font-size: 22px; font-weight: bold; color: #dc3545;">{{ $productosSinStock }}</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th style="width: 10%; text-align: center;">Código</th>
                <th style="width: 30%;">Producto</th>
                <th style="width: 15%;">Categoría</th>
                <th style="width: 10%;">Ubicación</th>
                <th style="width: 10%; text-align: right;">Stock</th>
                <th style="width: 10%; text-align: right;">Mínimo</th>
                <th style="width: 15%; text-align: center;">Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $product)
                @php
                    $stockClass = match(true) {
                        $product->stock == 0 => 'stock-empty',
                        $product->stock <= $product->min_stock => 'stock-low',
                        default => 'stock-optimal'
                    };
                    $stockLabel = match(true) {
                        $product->stock == 0 => 'SIN STOCK',
                        $product->stock <= $product->min_stock => 'BAJO STOCK',
                        default => 'ÓPTIMO'
                    };
                @endphp
                <tr>
                    <td>{{ $product->code }}</td>
                    <td><strong>{{ $product->name }}</strong></td>
                    <td>{{ $product->category->name ?? 'N/A' }}</td>
                    <td>{{ $product->location->name ?? 'N/A' }}</td>
                    <td class="text-right"><strong>{{ $product->stock }}</strong> {{ $product->unit->abbreviation ?? '' }}</td>
                    <td class="text-right">{{ $product->min_stock }}</td>
                    <td class="text-center">
                        <span class="{{ $stockClass }}">{{ $stockLabel }}</span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Documento generado el {{ now()->format('d/m/Y H:i') }} | Sistema de Gestión de Inventario - IAC</p>
    </div>
</body>
</html>
