@extends('adminlte::page')

@section('title', 'Maestros | Editar Ubicación')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="text-dark font-weight-bold" style="font-size: 1.75rem;">
                <i class="fas fa-warehouse text-primary mr-2"></i> Editar Ubicación
            </h1>
            <p class="text-muted mb-0">Modifique la información de la ubicación física o almacén.</p>
        </div>
        <a href="{{ route('admin.locations.index') }}" class="btn btn-secondary px-3 py-2" style="border-radius: 8px;">
            <i class="fas fa-arrow-left mr-1"></i> Volver al Listado
        </a>
    </div>
@stop

@section('content')
    @include('admin.partials.session-messages')

    <div class="row">
        <div class="col-lg-7 col-md-9 mx-auto">
            <div class="card p-4 bg-white" style="border-radius: 12px; border: 1px solid #e5e7eb; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                <form action="{{ route('admin.locations.update', $location) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <h5 class="font-weight-bold text-dark mb-4">
                        <i class="fas fa-edit text-warning mr-2"></i> Modificar Datos de la Ubicación
                    </h5>
                    
                    <div class="form-group mb-3">
                        <label for="name" class="text-xs font-weight-bold text-secondary text-uppercase mb-1 d-block">
                            Nombre de la Ubicación <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light" style="border-top-left-radius: 8px; border-bottom-left-radius: 8px;"><i class="fas fa-warehouse text-muted"></i></span>
                            </div>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $location->name) }}" placeholder="Ej: Almacén Principal, Refrigerador B, Estante A1" 
                                   style="border-top-right-radius: 8px; border-bottom-right-radius: 8px;" required>
                        </div>
                        @error('name')<span class="text-danger text-xs mt-1 d-block">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="details" class="text-xs font-weight-bold text-secondary text-uppercase mb-1 d-block">
                            Detalles / Descripción <span class="text-muted">(Opcional)</span>
                        </label>
                        <textarea name="details" id="details" rows="4" class="form-control @error('details') is-invalid @enderror" 
                                  placeholder="Escriba especificaciones físicas (Ej: Pasillo 3, Temperatura controlada)..." 
                                  style="border-radius: 8px;">{{ old('details', $location->details) }}</textarea>
                        @error('details')<span class="text-danger text-xs mt-1 d-block">{{ $message }}</span>@enderror
                    </div>

                    <div class="row mt-4 mb-2 bg-light p-3 rounded" style="font-size: 0.8rem; border: 1px solid #e5e7eb; border-radius: 8px;">
                        <div class="col-4">
                            <span class="text-muted d-block text-uppercase font-weight-bold" style="font-size: 0.65rem; letter-spacing: 0.5px;">Creado por:</span>
                            <span class="text-dark font-weight-bold">{{ $location->user->name ?? 'N/A' }}</span>
                        </div>
                        <div class="col-4">
                            <span class="text-muted d-block text-uppercase font-weight-bold" style="font-size: 0.65rem; letter-spacing: 0.5px;">Fecha creación:</span>
                            <span class="text-dark">{{ $location->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="col-4">
                            <span class="text-muted d-block text-uppercase font-weight-bold" style="font-size: 0.65rem; letter-spacing: 0.5px;">Última act.:</span>
                            <span class="text-dark">{{ $location->updated_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>

                    <hr style="border-top: 1px solid #e5e7eb; margin: 1.5rem 0;">

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('admin.locations.index') }}" class="btn btn-outline-secondary font-weight-bold px-4 py-2" style="border-radius: 8px;">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary font-weight-bold px-4 py-2" style="border-radius: 8px;">
                            <i class="fas fa-save mr-1"></i> Actualizar Ubicación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

