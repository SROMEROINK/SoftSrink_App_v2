// public/js/form-ajax-submit.js
(function () {
  function getCsrfToken() {
    const el = document.querySelector('meta[name="csrf-token"]');
    return el ? el.getAttribute('content') : null;
  }

  function highlightDuplicatedRows(form, duplicatedRows) {
  if (!Array.isArray(duplicatedRows) || duplicatedRows.length === 0) return;

  // buscamos la tabla dentro del mismo form
  const table = form.querySelector('#tablaListadoOF');
  if (!table) return;

  // limpiamos marcas anteriores
  table.querySelectorAll('tbody tr').forEach(tr => {
    tr.classList.remove('row-dup');
  });

  // marcamos filas (tu backend manda 1-based: 1,2,3...)
  duplicatedRows.forEach(n => {
    const tr = table.querySelector(`tbody tr:nth-child(${n})`);
    if (tr) tr.classList.add('row-dup');
  });

  // hacemos scroll a la primera fila duplicada
  const first = duplicatedRows.slice().sort((a,b)=>a-b)[0];
  const firstTr = table.querySelector(`tbody tr:nth-child(${first})`);
  if (firstTr) firstTr.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

  function formatDuplicatedRows(duplicatedRows) {
    if (!Array.isArray(duplicatedRows) || duplicatedRows.length === 0) return null;

    // Ordena y elimina repetidos por las dudas
    const rows = [...new Set(duplicatedRows)].sort((a, b) => a - b);

    return `
      <div style="text-align:left">
        <b>Se encontraron filas duplicadas:</b><br>
        <span>Filas: ${rows.join(', ')}</span><br><br>
        <small>Corregí esas filas y volvé a intentar.</small>
      </div>
    `;
  }

  function formatValidationErrors(errors) {
    if (!errors || typeof errors !== 'object') return null;

    // Convierte {campo: [msg1,msg2], ...} a lista HTML
    const lines = [];
    Object.keys(errors).forEach((field) => {
      const msgs = Array.isArray(errors[field]) ? errors[field] : [errors[field]];
      msgs.forEach((m) => lines.push(`• ${m}`));
    });

    if (!lines.length) return null;

    return `
      <div style="text-align:left">
        <b>Revisá los siguientes puntos:</b><br><br>
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

      // ✅ Validación 422 (Laravel)
      if (res.status === 422) {
        const html = formatValidationErrors(data?.errors);
        return Swal.fire({
          icon: 'error',
          title: 'Errores de validación',
          html: html || (data?.message || 'Errores de validación.'),
          confirmButtonText: 'Corregir'
        });
      }

      // ✅ Errores no OK (400/500/etc)
      if (!res.ok) {
        // ⭐ Caso especial: filas duplicadas (tu store de pedido_cliente)
        const dupHtml = formatDuplicatedRows(data?.duplicatedRows);
        if (dupHtml) {

          // ✅ MARCAR FILAS EN ROJO + SCROLL
          highlightDuplicatedRows(form, data?.duplicatedRows);

          return Swal.fire({
            icon: 'warning',
            title: 'Filas duplicadas',
            html: dupHtml,
            confirmButtonText: 'OK'
          });
        }

        return Swal.fire({
          icon: 'error',
          title: 'Error',
          text: data?.message || 'Ocurrió un error.',
          confirmButtonText: 'Entendido'
        });
      }

      // ✅ Caso especial: no_changes (por si un store/update lo usa)
      if (data?.type === 'no_changes') {
        return Swal.fire({
          icon: 'warning',
          title: 'Sin cambios',
          text: data?.message || 'No se detectaron cambios.',
          confirmButtonText: 'OK'
        });
      }

      // ✅ OK general
      await Swal.fire({
        icon: data?.success ? 'success' : 'error',
        title: data?.success ? 'Éxito' : 'Error',
        text: data?.message || (data?.success ? 'Operación realizada.' : 'Ocurrió un error.'),
        confirmButtonText: 'OK'
      });

      const goTo = data?.redirect || redirectUrl;
      if (data?.success && goTo) window.location.href = goTo;

    } catch (err) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'No se pudo conectar con el servidor.',
        confirmButtonText: 'OK'
      });
    }
  });
})();