@extends('adminlte::page')

@section('title', 'Crear Nueva Solicitud')

@section('content_header')
    <h1 class="m-0 text-dark">Crear Solicitud de Salida</h1>
@stop

@section('content')
    {{-- BLOQUE DE ERRORES DE VALIDACIÓN --}}
    @if ($errors->any())
        <x-adminlte-alert theme="danger" title="Error de Validación">
            Parece que hay problemas con la información proporcionada:
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-adminlte-alert>
    @endif
    
    <x-adminlte-card title="Detalle de la Solicitud" icon="fas fa-file-invoice" theme="primary">
        <form action="{{ route('admin.requests.store') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-md-6">
                    {{-- Campo de Referencia o Proyecto --}}
                    <x-adminlte-input name="reference" label="Referencia / Proyecto" placeholder="Ej: Proyecto 'Reemplazo de Equipos'" value="{{ old('reference') }}" required/>
                </div>
                <div class="col-md-6">
                    {{-- Campo de Solicitante --}}
                    <x-adminlte-input name="user_name" label="Solicitante" value="{{ auth()->user()->name }}" disabled/>
                    {{-- El ID del solicitante se pasa al controlador, usando 'requester_id' en el controlador --}}
                    <input type="hidden" name="user_id" value="{{ auth()->id() }}"> 
                </div>
            </div>

            <div class="row">
                {{-- 🔑 CAMPO REQUERIDO: Justificación --}}
                <div class="col-md-6">
                    <x-adminlte-textarea name="justification" label="Justificación de la Solicitud" rows="3" placeholder="Detalle el motivo del requerimiento." required>{{ old('justification') }}</x-adminlte-textarea>
                </div>
                {{-- 🔑 CAMPO REQUERIDO/OPCIONAL: Área de Destino --}}
                <div class="col-md-6">
                    <x-adminlte-input name="destination_area" label="Área de Destino" placeholder="Ej: Mantenimiento, Taller B, Oficina Central" value="{{ old('destination_area') }}" />
                </div>
            </div>

            {{-- -------------------------- ÍTEMS SOLICITADOS (Productos y Kits) -------------------------- --}}
            <h5 class="mt-4"><i class="fas fa-list-ul"></i> Ítems a Solicitar</h5>
            <div id="items-container">
                {{-- Los ítems se insertarán aquí dinámicamente --}}
            </div>

            <button type="button" class="btn btn-sm btn-info mt-2" id="add-item-btn"><i class="fas fa-plus"></i> Agregar Producto / Kit</button>
            
            <hr>

            <x-adminlte-button class="btn-flat" type="submit" label="Enviar Solicitud" theme="success" icon="fas fa-lg fa-paper-plane"/>
            <a href="{{ route('admin.requests.index') }}" class="btn btn-flat btn-default">Cancelar</a>
        </form>
    </x-adminlte-card>
@stop

{{-- Incluimos el template JS. Asegúrate de que 'admin.requests.partials.item_row_template' existe --}}
@include('admin.requests.partials.item_row_template', ['products' => $products, 'kits' => $kits])

@push('js')
<script>
    let itemIndex = 0;

    // Función para añadir una nueva fila de ítem
    function addItemRow() {
        // 1. Clonar el template
        const template = $('#item-row-template').html();
        let newRow = template.replace(/__INDEX__/g, itemIndex);
        
        // 2. Insertar la fila
        $('#items-container').append(newRow);

        // 3. Inicializar Select2 en los nuevos selectores si usas ese plugin (IMPORTANTE)
        $('#item_select_' + itemIndex).select2();
        $('#type_select_' + itemIndex).select2({ minimumResultsForSearch: Infinity }); // Oculta la búsqueda para Product/Kit

        // 4. Configurar listener para el cambio de tipo (Producto/Kit)
        $('#type_select_' + itemIndex).on('change', function() {
            const index = $(this).data('index');
            const type = $(this).val();
            
            // Ocultar/Mostrar los selectores
            $('#product_selector_' + index).toggle(type === 'product');
            $('#kit_selector_' + index).toggle(type === 'kit');

            // Limpiar valores del selector oculto para evitar errores en el backend
            // Solo limpiamos los selectores que contienen el ID real que Laravel valida (product_id o kit_id)
            if (type === 'product') {
                $('#kit_id_' + index).val(''); // Limpia el input oculto o select del kit
            } else {
                $('#product_id_' + index).val(''); // Limpia el input oculto o select del producto
            }
        }).trigger('change'); // Ejecutar al inicio para establecer la visibilidad

        itemIndex++;
    }

    // Listener para el botón de agregar
    $('#add-item-btn').on('click', function() {
        addItemRow();
    });

    // Listener para el botón de eliminar
    $('#items-container').on('click', '.remove-item-btn', function() {
        $(this).closest('.item-row').remove();
    });

    // Inicializar con al menos una fila
    addItemRow();
</script>
@endpush