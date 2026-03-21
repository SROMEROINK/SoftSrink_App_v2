(function () {
  if (typeof window === 'undefined' || typeof window.Swal === 'undefined' || window.SwalUtils) {
    return;
  }

  const originalFire = window.Swal.fire.bind(window.Swal);
  const baseOptions = {
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'OK',
    cancelButtonText: 'Cancelar'
  };

  function fire(options) {
    return originalFire({ ...baseOptions, ...options });
  }

  window.Swal.fire = function (...args) {
    if (args.length === 1 && args[0] && typeof args[0] === 'object' && !Array.isArray(args[0])) {
      return fire(args[0]);
    }

    return originalFire(...args);
  };

  window.SwalUtils = {
    fire,
    noChanges(text) {
      return fire({
        icon: 'warning',
        title: 'Sin cambios',
        text: text || 'No se detectaron cambios en el formulario.'
      });
    },
    validation(html) {
      return fire({
        icon: 'error',
        title: 'Errores de validacion',
        html: html
      });
    },
    created(text) {
      return fire({
        icon: 'success',
        title: 'Creado',
        text: text || 'Registro creado correctamente.'
      });
    },
    updated(text) {
      return fire({
        icon: 'success',
        title: 'Actualizado',
        text: text || 'Registro actualizado correctamente.'
      });
    },
    deleted(text) {
      return fire({
        icon: 'success',
        title: 'Eliminado',
        text: text || 'Registro eliminado correctamente.'
      });
    },
    restored(text) {
      return fire({
        icon: 'success',
        title: 'Restaurado',
        text: text || 'Registro restaurado correctamente.'
      });
    },
    error(text) {
      return fire({
        icon: 'error',
        title: 'Error',
        text: text || 'Ocurrio un error.'
      });
    },
    confirmDelete(text) {
      return fire({
        icon: 'warning',
        title: 'Eliminar registro?',
        text: text || 'El registro sera enviado a eliminados.',
        showCancelButton: true,
        confirmButtonText: 'Si, eliminar',
        cancelButtonText: 'Cancelar'
      });
    },
    confirmRestore(text) {
      return fire({
        icon: 'question',
        title: 'Restaurar registro?',
        text: text || 'El registro volvera al listado principal.',
        showCancelButton: true,
        confirmButtonText: 'Si, restaurar',
        cancelButtonText: 'Cancelar'
      });
    }
  };
})();
