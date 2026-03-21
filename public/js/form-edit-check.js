/**
 * FormEditCheck - Script global reutilizable para detectar cambios en formularios de edicion.
 */
(function ($) {
    'use strict';

    const defaults = {
        noChangesMessage: 'No se detectaron cambios en el formulario.',
        successMessage: 'Registro actualizado correctamente.',
        errorMessage: 'Ocurrio un error al actualizar el registro.',
        excludeFields: ['_token', '_method', 'csrf_token']
    };

    function getSwalUtils() {
        return typeof window !== 'undefined' ? window.SwalUtils || null : null;
    }

    function showNoChanges(text) {
        const swalUtils = getSwalUtils();
        if (swalUtils) {
            return swalUtils.noChanges(text);
        }

        if (typeof Swal !== 'undefined') {
            return Swal.fire({
                icon: 'warning',
                title: 'Sin cambios',
                text: text,
                showConfirmButton: true
            });
        }

        alert(text);
        return Promise.resolve();
    }

    function showSuccess(text) {
        const swalUtils = getSwalUtils();
        if (swalUtils) {
            return swalUtils.updated(text);
        }

        if (typeof Swal !== 'undefined') {
            return Swal.fire({
                icon: 'success',
                title: 'Actualizado',
                text: text,
                showConfirmButton: false,
                timer: 1500
            });
        }

        alert(text);
        return Promise.resolve();
    }

    function showError(text) {
        const swalUtils = getSwalUtils();
        if (swalUtils) {
            return swalUtils.error(text);
        }

        if (typeof Swal !== 'undefined') {
            return Swal.fire({
                icon: 'error',
                title: 'Error',
                text: text,
                showConfirmButton: true
            });
        }

        alert(text);
        return Promise.resolve();
    }

    function getFieldValue($field) {
        const tagName = $field.prop('tagName').toLowerCase();
        const type = $field.attr('type') || '';

        if (type === 'checkbox') {
            return $field.is(':checked') ? '1' : '0';
        }

        if (type === 'radio') {
            const name = $field.attr('name');
            return $('input[name="' + name + '"]:checked').val() || '';
        }

        if (tagName === 'select' && $field.attr('multiple')) {
            return $field.val() ? $field.val().sort().join(',') : '';
        }

        return String($field.val() || '').trim();
    }

    function getEditableFields($form) {
        const fields = {};
        const excludeFields = $form.data('exclude-fields')
            ? $form.data('exclude-fields').split(',').map(f => f.trim())
            : defaults.excludeFields;

        $form.find('input, select, textarea').each(function () {
            const $field = $(this);
            const name = $field.attr('name');
            const id = $field.attr('id');
            const fieldId = id || name;

            if (!fieldId ||
                excludeFields.includes(name) ||
                excludeFields.includes(fieldId) ||
                ($field.prop('disabled') && $field.attr('type') !== 'hidden') ||
                ($field.prop('readonly') && $field.attr('type') !== 'hidden')) {
                return;
            }

            if ($field.attr('type') === 'checkbox' || $field.attr('type') === 'radio') {
                if (!fields[name]) {
                    fields[name] = getFieldValue($field);
                }
            } else {
                fields[fieldId] = getFieldValue($field);
            }
        });

        return fields;
    }

    function hasChanges(originalValues, currentValues) {
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

    function handleAjaxResponse(response, $form) {
        if (response.type === 'no_changes' || (!response.success && response.warning)) {
            const customMessage = $form.data('no-changes-message');
            const message = customMessage || response.message || response.warning || defaults.noChangesMessage;
            showNoChanges(message);
            return;
        }

        if (response.success) {
            const customMessage = $form.data('success-message');
            const redirectUrl = $form.data('redirect-url') || response.redirect;
            const message = customMessage || response.message || defaults.successMessage;

            showSuccess(message).then(() => {
                if (redirectUrl) {
                    window.location.href = redirectUrl;
                }
            });
            return;
        }

        showError(response.message || defaults.errorMessage);
    }

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
            success: function (response) {
                handleAjaxResponse(response, $form);
            },
            error: function (xhr) {
                let errorMessage = defaults.errorMessage;

                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.errors) {
                        errorMessage = Object.values(xhr.responseJSON.errors).flat().join('\n');
                    }
                }

                showError(errorMessage);
            }
        });
    }

    function initFormEditCheck($form) {
        const originalValues = getEditableFields($form);

        $form.off('submit.formEditCheck').on('submit.formEditCheck', function (e) {
            const currentValues = getEditableFields($form);

            if (!hasChanges(originalValues, currentValues)) {
                e.preventDefault();
                const customMessage = $form.data('no-changes-message');
                showNoChanges(customMessage || defaults.noChangesMessage);
                return false;
            }

            e.preventDefault();
            submitFormAjax($form);
            return false;
        });
    }

    $(document).ready(function () {
        $('form[data-edit-check="true"]').each(function () {
            initFormEditCheck($(this));
        });
    });

    window.FormEditCheck = {
        init: function (selector) {
            $(selector).each(function () {
                initFormEditCheck($(this));
            });
        }
    };
})(jQuery);
