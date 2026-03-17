/**
 * FormEditCheck - Script global reutilizable para detectar cambios en formularios de edición
 * 
 * Uso:
 * 1. Incluir este script en la vista edit.blade.php
 * 2. Agregar data-attribute al form: <form data-edit-check="true" ...>
 * 3. Opcional: especificar campos a excluir: data-exclude-fields="campo1,campo2"
 * 4. Opcional: especificar mensaje personalizado: data-no-changes-message="Tu mensaje"
 * 5. Opcional: especificar URL de redirección: data-redirect-url="{{ route('...') }}"
 * 6. Opcional: especificar mensaje de éxito: data-success-message="Tu mensaje"
 * 
 * Ejemplo:
 * <form action="..." method="POST" data-edit-check="true" data-exclude-fields="_token,_method" data-redirect-url="{{ route('modelo.index') }}">
 *     ...
 * </form>
 */

(function($) {
    'use strict';

    // Configuración por defecto
    const defaults = {
        noChangesMessage: {
            icon: 'warning',
            title: 'Sin cambios',
            text: 'No se detectaron cambios en el formulario.',
            showConfirmButton: true
        },
        successMessage: {
            icon: 'success',
            title: 'Actualizado',
            text: 'Registro actualizado correctamente.',
            showConfirmButton: false,
            timer: 1500
        },
        errorMessage: {
            icon: 'error',
            title: 'Error',
            text: 'Ocurrió un error al actualizar el registro.',
            showConfirmButton: true
        },
        excludeFields: ['_token', '_method', 'csrf_token']
    };

    /**
     * Obtiene el valor de un campo según su tipo
     */
    function getFieldValue($field) {
        const tagName = $field.prop('tagName').toLowerCase();
        const type = $field.attr('type') || '';

        // Checkbox
        if (type === 'checkbox') {
            return $field.is(':checked') ? '1' : '0';
        }

        // Radio
        if (type === 'radio') {
            const name = $field.attr('name');
            return $('input[name="' + name + '"]:checked').val() || '';
        }

        // Select múltiple
        if (tagName === 'select' && $field.attr('multiple')) {
            return $field.val() ? $field.val().sort().join(',') : '';
        }

        // Select, input, textarea normal
        return String($field.val() || '').trim();
    }

    /**
     * Obtiene todos los campos editables del formulario
     */
    function getEditableFields($form) {
        const fields = {};
        const excludeFields = $form.data('exclude-fields') 
            ? $form.data('exclude-fields').split(',').map(f => f.trim())
            : defaults.excludeFields;

        // Obtener todos los campos del formulario
        $form.find('input, select, textarea').each(function() {
            const $field = $(this);
            const name = $field.attr('name');
            const id = $field.attr('id');
            const fieldId = id || name;

            // Excluir campos disabled, readonly (excepto si son hidden), y campos en la lista de exclusión
            if (!fieldId || 
                excludeFields.includes(name) || 
                excludeFields.includes(fieldId) ||
                ($field.prop('disabled') && $field.attr('type') !== 'hidden') ||
                ($field.prop('readonly') && $field.attr('type') !== 'hidden')) {
                return;
            }

            // Para checkboxes y radios, usar el name como identificador
            if ($field.attr('type') === 'checkbox' || $field.attr('type') === 'radio') {
                if (!fields[name]) {
                    fields[name] = getFieldValue($field);
                }
            } else if (fieldId) {
                fields[fieldId] = getFieldValue($field);
            }
        });

        return fields;
    }

    /**
     * Compara valores originales con valores actuales
     */
    function hasChanges(originalValues, currentValues) {
        // Comparar por claves
        const allKeys = new Set([...Object.keys(originalValues), ...Object.keys(currentValues)]);

        for (const key of allKeys) {
            const original = String(originalValues[key] || '').trim();
            const current = String(currentValues[key] || '').trim();

            if (original !== current) {
                return true;
            }
        }

        return false;
    }

    /**
     * Maneja la respuesta AJAX del servidor
     */
    function handleAjaxResponse(response, $form) {
        // Verificar si hay un tipo de respuesta especial
        if (response.type === 'no_changes' || (!response.success && response.warning)) {
            // No hay cambios detectados en el servidor
            const customMessage = $form.data('no-changes-message');
            const message = customMessage 
                ? { ...defaults.noChangesMessage, text: customMessage }
                : { ...defaults.noChangesMessage, text: response.message || response.warning || defaults.noChangesMessage.text };

            if (typeof Swal !== 'undefined') {
                Swal.fire(message);
            } else {
                alert(message.text || 'No se detectaron cambios en el formulario.');
            }
            return;
        }

        // Éxito
        if (response.success) {
            const customMessage = $form.data('success-message');
            const redirectUrl = $form.data('redirect-url') || response.redirect;
            
            const message = customMessage
                ? { ...defaults.successMessage, text: customMessage }
                : { ...defaults.successMessage, text: response.message || defaults.successMessage.text };

            if (typeof Swal !== 'undefined') {
                Swal.fire(message).then(() => {
                    if (redirectUrl) {
                        window.location.href = redirectUrl;
                    }
                });
            } else {
                alert(message.text || 'Registro actualizado correctamente.');
                if (redirectUrl) {
                    window.location.href = redirectUrl;
                }
            }
            return;
        }

        // Error
        const errorMessage = response.message || defaults.errorMessage.text;
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                ...defaults.errorMessage,
                text: errorMessage
            });
        } else {
            alert(errorMessage);
        }
    }

    /**
     * Envía el formulario vía AJAX
     */
    function submitFormAjax($form) {
        const formData = $form.serialize();
        const url = $form.attr('action');
        const method = $form.find('input[name="_method"]').val() || $form.attr('method') || 'POST';

        $.ajax({
            url: url,
            type: method === 'PUT' || method === 'PATCH' || method === 'DELETE' ? 'POST' : method,
            data: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                handleAjaxResponse(response, $form);
            },
            error: function(xhr) {
                let errorMessage = defaults.errorMessage.text;
                
                // Intentar obtener mensaje de error de la respuesta
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        errorMessage = errors.join('\n');
                    }
                }

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        ...defaults.errorMessage,
                        text: errorMessage
                    });
                } else {
                    alert(errorMessage);
                }
            }
        });
    }

    /**
     * Inicializa la detección de cambios para un formulario
     */
    function initFormEditCheck($form) {
        // Almacenar valores originales al cargar la página
        const originalValues = getEditableFields($form);

        // Usar namespace para evitar duplicados
                $form.off('submit.formEditCheck').on('submit.formEditCheck', function(e) {
            const currentValues = getEditableFields($form);
            const hasFormChanges = hasChanges(originalValues, currentValues);

            if (!hasFormChanges) {
                e.preventDefault();

                const customMessage = $form.data('no-changes-message');
                const message = customMessage
                    ? { ...defaults.noChangesMessage, text: customMessage }
                    : defaults.noChangesMessage;

                if (typeof Swal !== 'undefined') {
                    Swal.fire(message);
                } else {
                    alert(message.text || 'No se detectaron cambios en el formulario.');
                }

                return false;
            }

            // Hay cambios: enviar por AJAX para poder mostrar SweetAlert de éxito
            e.preventDefault();
            submitFormAjax($form);
            return false;
        });
    }

    // Inicializar cuando el documento esté listo
    $(document).ready(function() {
        $('form[data-edit-check="true"]').each(function() {
            initFormEditCheck($(this));
        });
    });

    // También permitir inicialización manual
    window.FormEditCheck = {
        init: function(selector) {
            $(selector).each(function() {
                initFormEditCheck($(this));
            });
        }
    };

})(jQuery);
