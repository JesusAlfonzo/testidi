<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Stock</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .header { text-align: center; margin-bottom: 30px; }
        .badge-danger { color: red; font-weight: bold; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #666; }
    </style>
</head>
<body>

    <div class="header">
        <h2>Instituto de Inmunología Dr. Nicolás Bianco</h2>
        <h3>Reporte de Inventario y Stock Actual</h3>
        <p>Generado el: {{ date('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Producto</th>
                <th>Categoría</th>
                <th>Ubicación</th>
                <th>Stock</th>
                <th>Mínimo</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $product)
                @php
                    $isLow = $product->stock <= $product->min_stock;
                @endphp
                <tr>
                    <td>{{ $product->code }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->category->name ?? '-' }}</td>
                    <td>{{ $product->location->name ?? '-' }}</td>
                    <td>{{ $product->stock }} {{ $product->unit->abbreviation ?? '' }}</td>
                    <td>{{ $product->min_stock }}</td>
                    <td>
                        @if($isLow)
                            <span class="badge-danger">BAJO STOCK</span>
                        @else
                            Óptimo
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Sistema de Gestión de Inventario SGCI-IDI
    </div>

</body>
</html>