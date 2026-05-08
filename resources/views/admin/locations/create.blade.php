@extends('adminlte::page')

@section('title', 'Crear Ubicación')

@section('content_header')
    <h1>Crear Nueva Ubicación</h1>
@stop

@section('content')
    @include('admin.partials.session-messages')

    <div class="row">
        <div class="col-12">
            <div class="card" style="border-left: 4px solid #8b5cf6;">
                <div class="card-header" style="background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%);">
                    <h3 class="card-title text-white">
                        <i class="fas fa-map-marker-alt"></i> Datos de la Ubicación
                    </h3>
                </div>
                <form action="{{ route('admin.locations.store') }}" method="POST">
                    @csrf
                    <div class="card-body">

                        {{-- Información Principal --}}
                        <div class="card" style="border-left: 4px solid #8b5cf6;">
                            <div class="card-header" style="background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%);">
                                <h3 class="card-title text-white">
                                    <i class="fas fa-info-circle"></i> Información Principal
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 col-md-8">
                                        <div class="form-group">
                                            <label for="name">Nombre <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-purple text-white"><i class="fas fa-warehouse"></i></span>
                                                </div>
                                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Ej: Almacén Principal, Anaquel A1" required>
                                            </div>
                                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            <small class="form-text text-muted">Nombre único que identificará esta ubicación física en el inventario.</small>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label>Vista Previa</label>
                                            <div class="p-3 border rounded bg-light text-center">
                                                <span id="previewBadge" class="badge badge-purple" style="font-size: 1rem; padding: 0.5rem 1rem;">
                                                    <i class="fas fa-map-marker-alt"></i> <span id="previewText">Nombre</span>
                                                </span>
                                                <small class="d-block text-muted mt-2">Así se verá la ubicación</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Detalles --}}
                        <div class="card" style="border-left: 4px solid #10b981;">
                            <div class="card-header" style="background: linear-gradient(135deg, #10b981 0%, #34d399 100%);">
                                <h3 class="card-title text-white">
                                    <i class="fas fa-file-alt"></i> Detalles
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group mb-0">
                                            <label for="details">Detalles <span class="text-muted">(Opcional)</span></label>
                                            <textarea name="details" id="details" rows="4" class="form-control @error('details') is-invalid @enderror" placeholder="Describa detalles específicos de la ubicación. Ej: Zona de congelación, Pasillo 3, Estante B.">{{ old('details') }}</textarea>
                                            @error('details')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            <small class="form-text text-muted">Información adicional que ayude a localizar físicamente esta ubicación.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Información del Registro --}}
                        <div class="card mb-0" style="border-left: 4px solid #6c757d;">
                            <div class="card-header" style="background: linear-gradient(135deg, #6c757d 0%, #8a939d 100%);">
                                <h3 class="card-title text-white">
                                    <i class="fas fa-clipboard-list"></i> Información del Registro
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 col-md-4">
                                        <p class="mb-1"><strong><i class="fas fa-user"></i> Creado por:</strong></p>
                                        <p class="text-muted">{{ auth()->user()->name }}</p>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <p class="mb-1"><strong><i class="fas fa-calendar"></i> Fecha:</strong></p>
                                        <p class="text-muted">{{ now()->format('d/m/Y H:i') }}</p>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <p class="mb-1"><strong><i class="fas fa-check-circle"></i> Estado:</strong></p>
                                        <span class="badge badge-success"><i class="fas fa-check"></i> Activo</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Ubicación
                        </button>
                        <a href="{{ route('admin.locations.index') }}" class="btn btn-default">
                            <i class="fas fa-arrow-left"></i> Volver al Listado
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    document.getElementById('name').addEventListener('keyup', function() {
        document.getElementById('previewText').textContent = this.value || 'Nombre';
    });
</script>
@stop
