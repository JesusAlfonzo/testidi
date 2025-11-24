<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        // Obtenemos los mismos datos que el reporte, pero sin paginación
        return Product::with('unit', 'category', 'location')
            ->where('is_active', true)
            ->orderBy('name', 'asc')
            ->get();
    }

    public function map($product): array
    {
        $estado = $product->stock <= $product->min_stock ? 'BAJO STOCK' : 'Óptimo';

        return [
            $product->code,
            $product->name,
            $product->category->name ?? 'N/A',
            $product->location->name ?? 'N/A',
            $product->stock,
            $product->unit->abbreviation ?? 'unid',
            $product->min_stock,
            number_format($product->cost, 2),
            number_format($product->price, 2),
            $estado,
        ];
    }

    public function headings(): array
    {
        return [
            'CÓDIGO',
            'PRODUCTO',
            'CATEGORÍA',
            'UBICACIÓN',
            'STOCK ACTUAL',
            'UNIDAD',
            'STOCK MÍNIMO',
            'COSTO ($)',
            'PRECIO ($)',
            'ESTADO',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Poner la primera fila en negrita
            1    => ['font' => ['bold' => true]],
        ];
    }
}