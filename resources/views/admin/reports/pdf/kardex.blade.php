<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Kardex de Producto</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 5px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .header { text-align: center; margin-bottom: 20px; }
        .in { color: green; }
        .out { color: red; }
        .balance { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Instituto de Inmunología Dr. Nicolás Bianco</h2>
        <h3>Kardex de Inventario: {{ $product->name }}</h3>
        <p>Código: {{ $product->code }} | Generado: {{ date('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 15%">Fecha</th>
                <th style="width: 10%">Tipo</th>
                <th style="width: 20%">Referencia</th>
                <th>Detalle/Nota</th>
                <th style="width: 10%">Entrada</th>
                <th style="width: 10%">Salida</th>
                <th style="width: 10%">Saldo</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($kardex as $mov)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($mov['date'])->format('d/m/Y H:i') }}</td>
                    <td>{{ $mov['type'] }}</td>
                    <td>{{ $mov['reference'] }}</td>
                    <td>{{ $mov['notes'] }}</td>
                    <td class="in">
                        {{ $mov['quantity'] > 0 ? '+' . $mov['quantity'] : '' }}
                    </td>
                    <td class="out">
                        {{ $mov['quantity'] < 0 ? $mov['quantity'] : '' }}
                    </td>
                    <td class="balance">{{ $mov['balance'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <div style="margin-top: 20px; text-align: right;">
        <strong>Stock Final: {{ end($kardex)['balance'] ?? 0 }} {{ $product->unit->abbreviation ?? '' }}</strong>
    </div>
</body>
</html>