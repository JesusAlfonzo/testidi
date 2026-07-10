@extends('adminlte::page')

@section('title', 'Lotes por Vencer (FEFO)')

@section('plugins.Datatables', true)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark"><i class="fas fa-hourglass-half mr-2 text-warning"></i> Lotes por Vencer (FEFO)</h1>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <div class="card card-outline card-warning shadow-sm" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header border-bottom py-3" style="background-color: #f8f9fa;">
                <h3 class="card-title font-weight-bold mb-0">
                    <i class="fas fa-list mr-2"></i> Lotes con vencimiento menor a 90 días
                </h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle" id="expiringTable">
                        <thead class="bg-light text-muted text-uppercase" style="font-size: 0.85rem;">
                            <tr>
                                <th style="width: 5%">#</th>
                                <th style="width: 35%">Producto</th>
                                <th style="width: 15%">N° de Lote</th>
                                <th style="width: 15%" class="text-center">Cant. Disponible</th>
                                <th style="width: 15%" class="text-center">Vencimiento</th>
                                <th style="width: 15%" class="text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($batches as $index => $batch)
                                @php
                                    $days = now()->startOfDay()->diffInDays($batch->expiration_date, false);
                                    
                                    if ($days < 0) {
                                        $badgeClass = 'badge-danger';
                                        $statusText = 'Vencido hace ' . abs($days) . ' días';
                                    } elseif ($days <= 30) {
                                        $badgeClass = 'badge-danger';
                                        $statusText = 'En ' . $days . ' días';
                                    } elseif ($days <= 60) {
                                        $badgeClass = 'badge-warning';
                                        $statusText = 'En ' . $days . ' días';
                                    } else {
                                        $badgeClass = 'badge-success';
                                        $statusText = 'En ' . $days . ' días';
                                    }
                                @endphp
                                <tr>
                                    <td class="text-muted">{{ $index + 1 }}</td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="font-weight-bold text-dark">{{ $batch->product->name }}</span>
                                            <small class="text-muted">{{ $batch->product->code }}</small>
                                        </div>
                                    </td>
                                    <td class="font-weight-bold">{{ $batch->batch_number ?? 'S/N' }}</td>
                                    <td class="text-center">
                                        <span class="badge badge-info" style="font-size: 0.9rem;">
                                            {{ $batch->quantity }}
                                            <small>{{ $batch->product->unit->abbreviation ?? 'und' }}</small>
                                        </span>
                                    </td>
                                    <td class="text-center font-weight-bold">
                                        {{ $batch->expiration_date->format('d/m/Y') }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $badgeClass }} px-2 py-1" style="font-size: 0.85rem;">
                                            @if($days < 0) <i class="fas fa-times-circle mr-1"></i> @else <i class="far fa-clock mr-1"></i> @endif
                                            {{ $statusText }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('#expiringTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
                },
                "order": [[4, "asc"]], // Sort by date ascending (assuming format can be sorted, but actually it's d/m/Y so let's sort by index)
                "columnDefs": [
                    { "orderable": false, "targets": 0 }
                ]
            });
        });
    </script>
@stop
