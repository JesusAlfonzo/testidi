@extends('adminlte::page')

@section('title', 'Maestros | Editar Categoría')

@section('content_header')
    <h1 class="m-0 text-dark">Editar Categoría: <strong>{{ $category->name }}</strong></h1>
@stop

@section('content')
    @include('admin.partials.session-messages')

    <div class="row">
        <div class="col-12">
            <div class="card" style="border-left: 4px solid #f59e0b;">
                <div class="card-header" style="background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);">
                    <h3 class="card-title text-dark">
                        <i class="fas fa-edit"></i> Actualizar Datos de Categoría
                    </h3>
                </div>

                <form action="{{ route('admin.categories.update', $category) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="card-body">

                        {{-- Información Principal --}}
                        <div class="card" style="border-left: 4px solid #06b6d4;">
                            <div class="card-header" style="background: linear-gradient(135deg, #06b6d4 0%, #22d3ee 100%);">
                                <h3 class="card-title text-white">
                                    <i class="fas fa-info-circle"></i> Información Principal
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12 col-md-8">
                                        <div class="form-group">
                                            <label for="name">Nombre de la Categoría <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-info text-white"><i class="fas fa-tag"></i></span>
                                                </div>
                                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $category->name) }}" placeholder="Ej: Insumos de Laboratorio, Equipos de Oficina" required>
                                            </div>
                                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            <small class="form-text text-muted">Nombre único que identificará a esta categoría en todo el sistema.</small>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label>Vista Previa</label>
                                            <div class="p-3 border rounded bg-light text-center">
                                                <span id="previewBadge" class="badge badge-info" style="font-size: 1rem; padding: 0.5rem 1rem;">
                                                    <i class="fas fa-tag"></i> <span id="previewText">{{ $category->name }}</span>
                                                </span>
                                                <small class="d-block text-muted mt-2">Así se ve actualmente la categoría</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Descripción --}}
                        <div class="card" style="border-left: 4px solid #10b981;">
                            <div class="card-header" style="background: linear-gradient(135deg, #10b981 0%, #34d399 100%);">
                                <h3 class="card-title text-white">
                                    <i class="fas fa-file-alt"></i> Descripción
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group mb-0">
                                            <label for="description">Descripción <span class="text-muted">(Opcional)</span></label>
                                            <textarea name="description" id="description" rows="4" class="form-control @error('description') is-invalid @enderror" placeholder="Describa brevemente qué tipo de productos agrupa esta categoría y su propósito dentro del inventario.">{{ old('description', $category->description) }}</textarea>
                                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            <small class="form-text text-muted">Ayudará a otros usuarios a entender el alcance y uso de esta categoría.</small>
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
                                        <p class="text-muted">{{ $category->user->name ?? 'N/A' }}</p>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <p class="mb-1"><strong><i class="fas fa-calendar"></i> Fecha de creación:</strong></p>
                                        <p class="text-muted">{{ $category->created_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <p class="mb-1"><strong><i class="fas fa-clock"></i> Última actualización:</strong></p>
                                        <p class="text-muted">{{ $category->updated_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-warning text-dark">
                            <i class="fas fa-sync-alt"></i> Actualizar Categoría
                        </button>
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-default">
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
        document.getElementById('previewText').textContent = this.value || '{{ $category->name }}';
    });
</script>
@stop
