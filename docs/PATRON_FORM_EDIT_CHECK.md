# Patrón Reusable: Form Edit Check con Detección de Cambios

Este documento describe el patrón unificado para formularios de edición que detectan cambios y manejan actualizaciones vía AJAX.

## Problema Resuelto

Evita el doble disparo de alertas cuando se presiona "Actualizar" sin cambios:
- Antes: se mostraban dos SweetAlert (uno de "Sin cambios" y otro de "Actualizado")
- Ahora: se muestra un solo SweetAlert apropiado según el caso

## Componentes

### 1. Backend: Trait CheckForChanges

El trait `App\Http\Controllers\Traits\CheckForChanges` proporciona el método `updateIfChanged()` que:
- Verifica si hay cambios usando `$model->isDirty()`
- Devuelve JSON con `type: 'no_changes'` cuando no hay cambios
- Devuelve JSON con `success: true` cuando hay cambios y se actualizó

**Uso en el Controlador:**

```php
use App\Http\Controllers\Traits\CheckForChanges;

class MiController extends Controller
{
    use CheckForChanges;

    public function update(Request $request, $id)
    {
        $model = MiModelo::findOrFail($id);
        $validatedData = $request->validate([...]);

        return $this->updateIfChanged($model, $validatedData, [
            'success_redirect' => route('mi_modelo.index'),
            'success_message' => 'Registro actualizado correctamente.',
            'no_changes_message' => 'No se realizaron cambios.',
            'set_updated_by' => true,
            'use_transaction' => false, // true si necesitas transacción
        ]);
    }
}
```

**Respuesta JSON del Backend:**

Cuando NO hay cambios:
```json
{
    "success": false,
    "type": "no_changes",
    "message": "No se realizaron cambios.",
    "warning": "No se realizaron cambios."
}
```

Cuando SÍ hay cambios:
```json
{
    "success": true,
    "message": "Registro actualizado correctamente.",
    "redirect": "http://..."
}
```

### 2. Frontend: Script form-edit-check.js

El script `public/js/form-edit-check.js` maneja:
1. Detección de cambios en el frontend (antes de enviar)
2. Envío AJAX del formulario
3. Manejo de respuestas del backend
4. Mostrar SweetAlert apropiados

**No requiere código JavaScript adicional en la vista** si usas los data-attributes.

### 3. Vista: edit.blade.php

**Estructura mínima requerida:**

```blade
@extends('adminlte::page')

@section('content')
    <form 
        action="{{ route('mi_modelo.update', $modelo->id) }}" 
        method="POST" 
        data-edit-check="true" 
        data-exclude-fields="_token,_method"
        data-redirect-url="{{ route('mi_modelo.index') }}"
        data-success-message="Registro actualizado correctamente">
        
        @csrf
        @method('PUT')
        
        {{-- Campos del formulario --}}
        
        <button type="submit" class="btn btn-primary">Actualizar</button>
        <a href="{{ route('mi_modelo.index') }}" class="btn btn-default">Cancelar</a>
    </form>
@stop

@section('js')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/form-edit-check.js') }}"></script>
{{-- NO agregar handlers de submit aquí --}}
@stop
```

## Data Attributes del Form

| Atributo | Requerido | Descripción |
|----------|-----------|-------------|
| `data-edit-check="true"` | **Sí** | Activa el script de detección de cambios |
| `data-exclude-fields` | **Sí** | Campos a excluir de la comparación (ej: `"_token,_method"`) |
| `data-redirect-url` | Recomendado | URL a la que redirigir después de éxito |
| `data-success-message` | Opcional | Mensaje personalizado de éxito |
| `data-no-changes-message` | Opcional | Mensaje personalizado cuando no hay cambios |

## Flujo Completo

### Caso 1: Sin cambios en el frontend
1. Usuario presiona "Actualizar" sin modificar campos
2. `form-edit-check.js` detecta que no hay cambios
3. Se muestra SweetAlert "Sin cambios" (frontend)
4. **NO se envía petición AJAX al servidor**

### Caso 2: Con cambios en el frontend, pero sin cambios en el backend
1. Usuario modifica campos y presiona "Actualizar"
2. `form-edit-check.js` detecta cambios → envía AJAX
3. Backend ejecuta `updateIfChanged()` → detecta que `isDirty()` es false
4. Backend devuelve `{success: false, type: 'no_changes', ...}`
5. `form-edit-check.js` detecta `response.type === 'no_changes'`
6. Se muestra SweetAlert "Sin cambios" (backend)
7. **NO se muestra mensaje de éxito**

### Caso 3: Con cambios reales
1. Usuario modifica campos y presiona "Actualizar"
2. `form-edit-check.js` detecta cambios → envía AJAX
3. Backend ejecuta `updateIfChanged()` → detecta que `isDirty()` es true
4. Backend guarda cambios y devuelve `{success: true, ...}`
5. `form-edit-check.js` detecta `response.success === true`
6. Se muestra SweetAlert de éxito
7. Se redirige a la URL especificada

## Ejemplos de Implementación

### Ejemplo 1: Vista Simple (MarcasInsumos)

```blade
<form 
    action="{{ route('marcas_insumos.update', $marca->Id_Marca) }}" 
    method="POST" 
    data-edit-check="true" 
    data-exclude-fields="_token,_method" 
    data-redirect-url="{{ route('marcas_insumos.index') }}" 
    data-success-message="Marca de insumo actualizada correctamente">
    
    @csrf
    @method('PUT')
    
    {{-- Campos --}}
    
    <button type="submit" class="btn btn-primary">Actualizar</button>
</form>

@section('js')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/form-edit-check.js') }}"></script>
@stop
```

### Ejemplo 2: Vista con Lógica JavaScript Adicional (MpIngreso)

```blade
<form 
    action="{{ route('mp_ingresos.update', $ingreso->Id_MP) }}" 
    method="POST" 
    data-edit-check="true" 
    data-exclude-fields="_token,_method" 
    data-redirect-url="{{ route('mp_ingresos.index') }}" 
    data-success-message="Ingreso de materia prima actualizado correctamente">
    
    @csrf
    @method('PUT')
    
    {{-- Campos --}}
    
    <button type="submit" class="btn btn-primary">Actualizar</button>
</form>

@section('js')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/form-edit-check.js') }}"></script>
<script>
$(document).ready(function() {
    // Lógica adicional específica del formulario
    // (cálculos automáticos, concatenaciones, etc.)
    // PERO NO handlers de submit
});
</script>
@stop
```

## Reglas Importantes

1. **NO agregar handlers de submit** en el script inline de la vista
2. **NO usar `$('form').on('submit', ...)`** en la vista cuando uses `data-edit-check="true"`
3. **Siempre incluir** `data-exclude-fields="_token,_method"` para excluir campos del sistema
4. **Usar el trait CheckForChanges** en el controlador para consistencia
5. El script usa namespace `submit.formEditCheck` para evitar conflictos

## Archivos Afectados en el Proyecto

### Vistas Actualizadas:
- `resources/views/materia_prima/ingresos/edit.blade.php`
- `resources/views/marcas_insumos/edit.blade.php`
- `resources/views/proveedores/edit.blade.php`

### Controladores que usan CheckForChanges:
- `app/Http/Controllers/MpIngresoController.php`
- `app/Http/Controllers/MarcasInsumosController.php`
- `app/Http/Controllers/ProveedorController.php`

### Archivos Core:
- `app/Http/Controllers/Traits/CheckForChanges.php` (trait actualizado)
- `public/js/form-edit-check.js` (script unificado)

## Migración de Vistas Existentes

Para migrar una vista existente que tiene handlers duplicados:

1. **Verificar que el controlador use CheckForChanges**
   - Si no lo usa, agregar el trait y actualizar el método `update()`

2. **Actualizar el form HTML:**
   - Agregar `data-edit-check="true"`
   - Agregar `data-exclude-fields="_token,_method"`
   - Agregar `data-redirect-url="{{ route('...') }}"`
   - Agregar `data-success-message="..."` (opcional)

3. **Actualizar la sección @section('js'):**
   - Mantener `@section('js')` con SweetAlert2 y form-edit-check.js
   - **ELIMINAR** todo el código que tenga `$('form').on('submit', ...)`
   - Mantener solo lógica específica del formulario (cálculos, etc.)

4. **Probar:**
   - Probar sin cambios → debe mostrar solo "Sin cambios"
   - Probar con cambios → debe mostrar solo "Actualizado" y redirigir
   - Verificar que `updated_at` NO cambie cuando no hay cambios

## Notas Técnicas

- El script usa `e.preventDefault()` siempre, por lo que el form nunca se envía de forma tradicional
- Los handlers usan namespace `submit.formEditCheck` para evitar conflictos
- El script detecta cambios comparando valores originales vs actuales (frontend)
- El backend también verifica cambios usando `isDirty()` (backend)
- La verificación en frontend es una optimización para evitar peticiones innecesarias
- La verificación en backend es la fuente de verdad definitiva

