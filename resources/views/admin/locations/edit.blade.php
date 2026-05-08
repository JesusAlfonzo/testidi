@extends('adminlte::page')

@section('title', 'Editar Ubicación')

@section('content_header')
    <h1>Editar Ubicación: <strong>{{ $location->name }}</strong></h1>
@stop

@section('content')
    @include('admin.partials.session-messages')

    <div class="row">
        <div class="col-12">
            <div class="card" style="border-left: 4px solid #f59e0b;">
                <div class="card-header" style="background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);">
                    <h3 class="card-title text-dark">
                        <i class="fas fa-edit"></i> Modificar Datos de Ubicación
                    </h3>
                </div>
                <form action="{{ route('admin.locations.update', $location) }}" method="POST">
                    @csrf
                    @method('PUT')
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
                                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $location->name) }}" placeholder="Ej: Almacén Principal, Anaquel A1" required>
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
                                                    <i class="fas fa-map-marker-alt"></i> <span id="previewText">{{ $location->name }}</span>
                                                </span>
                                                <small class="d-block text-muted mt-2">Así se ve actualmente la ubicación</small>
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
                                            <textarea name="details" id="details" rows="4" class="form-control @error('details') is-invalid @enderror" placeholder="Describa detalles específicos de la ubicación. Ej: Zona de congelación, Pasillo 3, Estante B.">{{ old('details', $location->details) }}</textarea>
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
                                        <p class="text-muted">{{ $location->user->name ?? 'N/A' }}</p>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <p class="mb-1"><strong><i class="fas fa-calendar"></i> Fecha de creación:</strong></p>
                                        <p class="text-muted">{{ $location->created_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <p class="mb-1"><strong><i class="fas fa-clock"></i> Última actualización:</strong></p>
                                        <p class="text-muted">{{ $location->updated_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-warning text-dark">
                            <i class="fas fa-sync-alt"></i> Actualizar Ubicación
                        </button>
                        <a href="{{ route('admin.locations.index') }}" class="btn btn-default">
                            <i class="fas fa-arrow-left"></i> Cancelar
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
        document.getElementById('previewText').textContent = this.value || '{{ $location->name }}';
    });
</script>
@stop
