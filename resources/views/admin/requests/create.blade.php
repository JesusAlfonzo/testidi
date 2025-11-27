@extends('adminlte::page')

@section('title', 'Crear Solicitud')

{{-- Plugins para Select2 --}}
@section('plugins.Select2', true)

@section('content_header')
    <h1><i class="fas fa-file-medical"></i> Crear Solicitud de Salida</h1>
@stop

@section('content')
    {{-- Mensajes de error de validación --}}
    @if ($errors->any())
        <x-adminlte-alert theme="danger" title="Error de Validación" dismissable>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-adminlte-alert>
    @endif

    <form action="{{ route('admin.requests.store') }}" method="POST" id="requestForm">
        @csrf
        <div class="row">
            <div class="col-lg-9">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Datos de la Solicitud</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            {{-- Referencia --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="reference">Referencia / Proyecto <span class="text-danger">*</span></label>
                                    <input type="text" name="reference" class="form-control" value="{{ old('reference') }}" placeholder="Ej: Proyecto X, Uso Diario Lab" required>
                                </div>
                            </div>
                            {{-- Destino --}}
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="destination_area">Área de Destino</label>
                                    <input type="text" name="destination_area" class="form-control" value="{{ old('destination_area') }}" placeholder="Ej: Laboratorio Central">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="justification">Justificación <span class="text-danger">*</span></label>
                            <textarea name="justification" class="form-control" rows="2" required placeholder="Explique brevemente el motivo...">{{ old('justification') }}</textarea>
                        </div>

                        <hr>
                        <h5 class="mb-3"><i class="fas fa-boxes"></i> Ítems a Solicitar</h5>
                        
                        {{-- Contenedor de ítems dinámicos --}}
                        <div id="items-container">
                            {{-- Si hay error de validación, repoblar los ítems --}}
                            @if(old('items'))
                                @foreach(old('items') as $index => $item)
                                    @include('admin.requests.partials.item_row_template', ['index' => $index, 'item' => $item])
                                @endforeach
                            @endif
                        </div>

                        <button type="button" class="btn btn-info btn-sm" id="add-item-btn">
                            <i class="fas fa-plus"></i> Agregar Ítem
                        </button>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Guardar Solicitud
                        </button>
                        <a href="{{ route('admin.requests.index') }}" class="btn btn-default float-right">Cancelar</a>
                    </div>
                </div>
            </div>
            
            {{-- Panel Informativo Lateral --}}
            <div class="col-lg-3">
                <div class="card card-secondary">
                    <div class="card-header">
                        <h3 class="card-title">Información</h3>
                    </div>
                    <div class="card-body">
                        <strong>Solicitante:</strong>
                        <p class="text-muted">{{ auth()->user()->name }}</p>
                        <hr>
                        <strong>Fecha:</strong>
                        <p class="text-muted">{{ date('d/m/Y') }}</p>
                        <hr>
                        <p class="small">Recuerde que la solicitud quedará en estado <b>Pendiente</b> hasta que un administrador la apruebe.</p>
                    </div>
                </div>
            </div>
        </div>
    </form>
@stop

{{-- Template JS para filas nuevas (usa el mismo partial pero vacío) --}}
<script id="item-row-template" type="text/template">
    @include('admin.requests.partials.item_row_template', ['index' => '__INDEX__', 'item' => []])
</script>

@section('js')
<script>
    $(document).ready(function() {
        let itemIndex = {{ count(old('items', [])) }};

        // Función para inicializar Select2 en una fila específica
        function initSelect2(row) {
            row.find('.select2').select2({
                theme: 'bootstrap4',
                width: '100%'
            });
        }

        // Función para agregar fila
        function addItemRow() {
            let template = $('#item-row-template').html();
            let newHtml = template.replace(/__INDEX__/g, itemIndex);
            let newRow = $(newHtml);
            
            $('#items-container').append(newRow);
            initSelect2(newRow);
            
            itemIndex++;
        }

        // Evento Click Agregar
        $('#add-item-btn').click(function() {
            addItemRow();
        });

        // Evento Click Eliminar (Delegado)
        $('#items-container').on('click', '.remove-item-btn', function() {
            $(this).closest('.item-row').remove();
        });

        // Evento Cambio de Tipo (Producto vs Kit)
        $('#items-container').on('change', '.type-selector', function() {
            let row = $(this).closest('.item-row');
            let type = $(this).val();
            
            if (type === 'product') {
                row.find('.container-product').show();
                row.find('.container-kit').hide();
                row.find('.select-kit').val('').trigger('change'); // Limpiar kit
            } else {
                row.find('.container-product').hide();
                row.find('.container-kit').show();
                row.find('.select-product').val('').trigger('change'); // Limpiar producto
            }
        });

        // Inicializar Select2 en filas existentes (si hubo old input)
        initSelect2($('#items-container'));

        // Agregar una fila inicial si está vacío
        if (itemIndex === 0) {
            addItemRow();
        }
    });
</script>
@endsection