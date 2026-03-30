@if (session('success') || session('error') || session('warning') || session('info'))
<script>
document.addEventListener('DOMContentLoaded', function () {
  const msg = {
    success: @json(session('success')),
    error: @json(session('error')),
    warning: @json(session('warning')),
    info: @json(session('info')),
  };

  if (typeof window === 'undefined' || typeof window.Swal === 'undefined') {
    return;
  }

  if (msg.success) {
    if (window.SwalUtils) {
      window.SwalUtils.updated(msg.success);
    } else {
      Swal.fire({ icon: 'success', title: 'Exito', text: msg.success, confirmButtonText: 'OK' });
    }
    return;
  }

  if (msg.warning) {
    if (window.SwalUtils) {
      window.SwalUtils.noChanges(msg.warning);
    } else {
      Swal.fire({ icon: 'warning', title: 'Atencion', text: msg.warning, confirmButtonText: 'OK' });
    }
    return;
  }

  if (msg.error) {
    if (window.SwalUtils) {
      window.SwalUtils.error(msg.error);
    } else {
      Swal.fire({ icon: 'error', title: 'Error', text: msg.error, confirmButtonText: 'OK' });
    }
    return;
  }

  if (msg.info) {
    Swal.fire({ icon: 'info', title: 'Informacion', text: msg.info, confirmButtonText: 'OK' });
  }
});
</script>
@endif
