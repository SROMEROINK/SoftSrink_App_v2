@if (session('success') || session('error') || session('warning') || session('info'))
<script>
document.addEventListener('DOMContentLoaded', function () {
  const msg = {
    success: @json(session('success')),
    error: @json(session('error')),
    warning: @json(session('warning')),
    info: @json(session('info')),
  };

  let icon = null, text = null, title = null;

  if (msg.success) { icon='success'; title='¡Éxito!'; text=msg.success; }
  else if (msg.error) { icon='error'; title='Error'; text=msg.error; }
  else if (msg.warning) { icon='warning'; title='Atención'; text=msg.warning; }
  else if (msg.info) { icon='info'; title='Info'; text=msg.info; }

  if (!icon) return;

  Swal.fire({
    icon,
    title,
    text,
    confirmButtonText: 'OK',
    timer: icon === 'success' ? 2500 : undefined,
    timerProgressBar: icon === 'success',
  });
});
</script>
@endif