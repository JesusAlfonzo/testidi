/**
 * Configuración unificada para DataTables
 * Este archivo contiene la configuración estándar para todos los DataTables del sistema
 */

(function() {
    'use strict';

    /**
     * Inicializa un DataTable con configuración estándar
     * @param {string} tableId - ID de la tabla
     * @param {string} route - Ruta para AJAX
     * @param {Object} options - Opciones adicionales
     */
    window.initDataTable = function(tableId, route, options = {}) {
        const defaults = {
            responsive: true,
            processing: true,
            serverSide: true,
            paging: true,
            lengthChange: true,
            searching: true,
            ordering: true,
            info: true,
            autoWidth: false,
            order: options.order || [[0, 'asc']],
            pageLength: 15,
            lengthMenu: [[15, 25, 50, 100], [15, 25, 50, 100]],
            language: {
                decimal: '',
                emptyTable: 'No hay información disponible',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                infoEmpty: 'Mostrando 0 a 0 de 0 registros',
                infoFiltered: '(Filtrado de _MAX_ total registros)',
                infoPostFix: '',
                thousands: ',',
                lengthMenu: 'Mostrar _MENU_ registros',
                loadingRecords: 'Cargando...',
                processing: 'Procesando...',
                search: 'Buscar:',
                zeroRecords: 'Sin resultados encontrados',
                paginate: {
                    first: 'Primero',
                    last: 'Último',
                    next: 'Siguiente',
                    previous: 'Anterior'
                }
            },
            columnDefs: options.columnDefs || [],
            filterForm: options.filterForm || 'filterForm',
            filters: options.filters || [],
            columns: options.columns || [],
            additionalAjaxData: options.additionalAjaxData || {}
        };

        const ajaxOptions = {
            url: route,
            type: 'GET',
            data: function(d) {
                // Agregar filtros dinámicos
                if (defaults.filters.length > 0) {
                    defaults.filters.forEach(function(filter) {
                        d[filter] = $(`select[name="${filter}"], input[name="${filter}"]`).val();
                    });
                }
                // Agregar datos adicionales
                Object.keys(defaults.additionalAjaxData).forEach(function(key) {
                    d[key] = defaults.additionalAjaxData[key];
                });
                return d;
            }
        };

        const config = {
            responsive: defaults.responsive,
            processing: defaults.processing,
            serverSide: defaults.serverSide,
            paging: defaults.paging,
            lengthChange: defaults.lengthChange,
            searching: defaults.searching,
            ordering: defaults.ordering,
            info: defaults.info,
            autoWidth: defaults.autoWidth,
            order: defaults.order,
            pageLength: defaults.pageLength,
            lengthMenu: defaults.lengthMenu,
            language: defaults.language,
            ajax: ajaxOptions,
            columns: defaults.columns,
            columnDefs: defaults.columnDefs
        };

        const table = $(`#${tableId}`).DataTable(config);

        // Configurar eventos de filtros
        if (defaults.filterForm && defaults.filters.length > 0) {
            const $form = $(`#${defaults.filterForm}`);
            
            // Evento change en selects e inputs
            $form.find('select, input').on('change', function() {
                table.draw();
            });

            // Evento submit para prevenir redirect
            $form.on('submit', function(e) {
                e.preventDefault();
                table.draw();
            });
        }

        // Ajuste responsive
        setTimeout(function() {
            table.columns.adjust().responsive.recalc();
        }, 500);

        return table;
    };

    /**
     * Agrega botón limpiar filtros a un DataTable
     * @param {string} tableId - ID de la tabla
     * @param {string} formId - ID del formulario de filtros
     * @param {string} clearUrl - URL para limpiar filtros (opcional)
     */
    window.addClearFiltersButton = function(tableId, formId, clearUrl) {
        const $form = $(`#${formId}`);
        const $table = $(`#${tableId}`);
        
        if ($form.length && $table.length) {
            // Agregar botón si no existe
            if ($form.find('#clearFilters').length === 0) {
                const clearBtn = `
                    <button type="button" class="btn btn-secondary" id="clearFilters">
                        <i class="fas fa-eraser"></i> Limpiar
                    </button>
                `;
                $form.find('button[type="submit"]').after(clearBtn);
            }

            $(document).on('click', '#clearFilters', function() {
                $form[0].reset();
                $form.find('select').trigger('change');
                $table.DataTable().draw();
            });
        }
    };

    /**
     * Inicializa Select2 con configuración estándar
     */
    window.initSelect2 = function() {
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%',
            placeholder: 'Seleccione una opción',
            allowClear: true
        });

        $(document).on('select2:open', function() {
            setTimeout(function() {
                var dropdown = document.querySelector('.select2-dropdown');
                if (dropdown) {
                    dropdown.style.maxHeight = '350px';
                    dropdown.style.overflow = 'hidden';
                    var results = dropdown.querySelector('.select2-results');
                    if (results) {
                        results.style.maxHeight = '350px';
                        results.style.overflowY = 'auto';
                    }
                }
            }, 10);
        });
    };

    /**
     * Configuración de DataTable para tablas client-side (sin AJAX)
     */
    window.initClientSideDataTable = function(tableId, options = {}) {
        const defaults = {
            responsive: true,
            paging: true,
            lengthChange: true,
            searching: true,
            ordering: true,
            info: true,
            autoWidth: false,
            order: options.order || [[0, 'asc']],
            language: {
                decimal: '',
                emptyTable: 'No hay información disponible',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                infoEmpty: 'Mostrando 0 a 0 de 0 registros',
                infoFiltered: '(Filtrado de _MAX_ total registros)',
                lengthMenu: 'Mostrar _MENU_ registros',
                search: 'Buscar:',
                zeroRecords: 'Sin resultados encontrados',
                paginate: {
                    first: 'Primero',
                    last: 'Último',
                    next: 'Siguiente',
                    previous: 'Anterior'
                }
            },
            columnDefs: options.columnDefs || []
        };

        return $(`#${tableId}`).DataTable(defaults);
    };

})();
