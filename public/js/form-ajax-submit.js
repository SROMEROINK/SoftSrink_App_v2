// public/js/form-ajax-submit.js
(function () {
  function getCsrfToken() {
    const el = document.querySelector('meta[name="csrf-token"]');
    return el ? el.getAttribute('content') : null;
  }

  function getSwalUtils() {
    return typeof window !== 'undefined' ? window.SwalUtils || null : null;
  }

  function showValidation(html) {
    const swalUtils = getSwalUtils();
    if (swalUtils) {
      return swalUtils.validation(html);
    }

    return Swal.fire({
      icon: 'error',
      title: 'Errores de validacion',
      html: html,
      confirmButtonText: 'Corregir'
    });
  }

  function showError(text) {
    const swalUtils = getSwalUtils();
    if (swalUtils) {
      return swalUtils.error(text);
    }

    return Swal.fire({
      icon: 'error',
      title: 'Error',
      text: text,
      confirmButtonText: 'OK'
    });
  }

  function showSuccess(text) {
    const swalUtils = getSwalUtils();
    if (swalUtils) {
      return swalUtils.created(text);
    }

    return Swal.fire({
      icon: 'success',
      title: 'Creado',
      text: text,
      confirmButtonText: 'OK'
    });
  }

  function showNoChanges(text) {
    const swalUtils = getSwalUtils();
    if (swalUtils) {
      return swalUtils.noChanges(text);
    }

    return Swal.fire({
      icon: 'warning',
      title: 'Sin cambios',
      text: text,
      confirmButtonText: 'OK'
    });
  }

  function highlightDuplicatedRows(form, duplicatedRows) {
    if (!Array.isArray(duplicatedRows) || duplicatedRows.length === 0) return;

    const table = form.querySelector('#tablaListadoOF');
    if (!table) return;

    table.querySelectorAll('tbody tr').forEach(tr => {
      tr.classList.remove('row-dup');
    });

    duplicatedRows.forEach(n => {
      const tr = table.querySelector(`tbody tr:nth-child(${n})`);
      if (tr) tr.classList.add('row-dup');
    });

    const first = duplicatedRows.slice().sort((a, b) => a - b)[0];
    const firstTr = table.querySelector(`tbody tr:nth-child(${first})`);
    if (firstTr) firstTr.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }

  function highlightInvalidRows(form, invalidRows) {
    if (!Array.isArray(invalidRows) || invalidRows.length === 0) return;

    const table = form.querySelector('#tablaListadoOF');
    if (!table) return;

    invalidRows.forEach(n => {
      const tr = table.querySelector(`tbody tr:nth-child(${n})`);
      if (!tr) return;

      tr.classList.add('row-invalid');
      const quickInput = tr.querySelector('.input-busqueda-rapida');
      if (quickInput) {
        quickInput.classList.add('input-invalid');
      }
    });

    const first = invalidRows.slice().sort((a, b) => a - b)[0];
    const firstTr = table.querySelector(`tbody tr:nth-child(${first})`);
    if (firstTr) {
      firstTr.scrollIntoView({ behavior: 'smooth', block: 'center' });
      const quickInput = firstTr.querySelector('.input-busqueda-rapida');
      if (quickInput) quickInput.focus();
    }
  }

  function formatDuplicatedRows(duplicatedRows) {
    if (!Array.isArray(duplicatedRows) || duplicatedRows.length === 0) return null;

    const rows = [...new Set(duplicatedRows)].sort((a, b) => a - b);

    return `
      <div style="text-align:left">
        <b>Se encontraron filas duplicadas:</b><br>
        <span>Filas: ${rows.join(', ')}</span><br><br>
        <small>Corregi esas filas y volve a intentar.</small>
      </div>
    `;
  }

  function formatValidationErrors(errors) {
    if (!errors || typeof errors !== 'object') return null;

    const lines = [];
    Object.keys(errors).forEach((field) => {
      const msgs = Array.isArray(errors[field]) ? errors[field] : [errors[field]];
      msgs.forEach((message) => lines.push(`- ${message}`));
    });

    if (!lines.length) return null;

    return `
      <div style="text-align:left">
        <b>Revisa los siguientes puntos:</b><br><br>
        ${lines.join('<br>')}
      </div>
    `;
  }

  document.addEventListener('submit', async function (e) {
    const form = e.target;
    if (!form.matches('form[data-ajax="true"]')) return;

    e.preventDefault();

    const url = form.getAttribute('action');
    const method = (form.getAttribute('method') || 'POST').toUpperCase();
    const redirectUrl = form.dataset.redirectUrl || null;
    const fd = new FormData(form);

    try {
      const res = await fetch(url, {
        method: method === 'GET' ? 'POST' : method,
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': getCsrfToken()
        },
        body: fd
      });

      const data = await res.json().catch(() => ({}));

      if (res.status === 422) {
        const html = formatValidationErrors(data?.errors) || (data?.message || 'Errores de validacion.');
        return showValidation(html);
      }

      if (!res.ok) {
        const dupHtml = formatDuplicatedRows(data?.duplicatedRows);
        if (dupHtml) {
          highlightDuplicatedRows(form, data?.duplicatedRows);
          return Swal.fire({
            icon: 'warning',
            title: 'Filas duplicadas',
            html: dupHtml,
            confirmButtonText: 'OK'
          });
        }

        if (Array.isArray(data?.invalidRows) && data.invalidRows.length) {
          highlightInvalidRows(form, data.invalidRows);
        }

        return showError(data?.message || 'Ocurrio un error.');
      }

      if (data?.type === 'no_changes') {
        return showNoChanges(data?.message || 'No se detectaron cambios.');
      }

      await showSuccess(data?.message || 'Operacion realizada correctamente.');

      const goTo = data?.redirect || redirectUrl;
      if (data?.success && goTo) {
        window.location.href = goTo;
      }
    } catch (err) {
      showError('No se pudo conectar con el servidor.');
    }
  });
})();
