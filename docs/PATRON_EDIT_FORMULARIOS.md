# Patrón Global para Formularios de EDIT

## Resumen

Este documento describe el patrón estandarizado implementado para todos los formularios de edición en el proyecto. El patrón previene actualizaciones innecesarias cuando el usuario no ha modificado ningún campo.

## Componentes del Patrón

### 1. JavaScript Global (`public/js/form-edit-check.js`)

Script reutilizable que detecta cambios en formularios antes de enviarlos.

**Características:**
- Detecta cambios en inputs, selects, textareas, checkboxes y radios
- Ignora campos disabled y readonly (excepto hidden)
- Permite excluir campos específicos mediante data-attributes
- Muestra SweetAlert2 cuando no hay cambios
- No interfiere con la lógica AJAX existente de cada vista

### 2. Trait para Controladores (`app/Http/Controllers/Traits/CheckForChanges.php`)

Trait reutilizable que valida cambios en el backend usando `isDirty()` de Eloquent.

**Características:**
- Valida cambios usando `isDirty()` después de `fill()`
- Soporta transacciones de base de datos
- Normalización opcional de datos
- Respuestas para AJAX y redirecciones normales
- Establece `updated_by` automáticamente si existe el campo

## Implementación en Vistas Edit

### Paso 1: Agregar data-attributes al formulario

```blade
<form action="{{ route('modelo.update', $modelo->id) }}" method="POST" 
      data-edit-check="true" 
      data-exclude-fields="_token,_method">
    @csrf
    @method('PUT')
    <!-- campos del formulario -->
</form>
```

**Data-attributes disponibles:**
- `data-edit-check="true"` - **Requerido**: Activa la detección de cambios
- `data-exclude-fields="campo1,campo2"` - Campos a excluir de la comparación (por defecto: `_token,_method`)
- `data-no-changes-message="Mensaje personalizado"` - Mensaje personalizado para SweetAlert

### Paso 2: Incluir el script global

```blade
@section('js')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/form-edit-check.js') }}"></script>
<script>
$(document).ready(function() {
    // Tu código AJAX existente aquí
    $('form[data-edit-check="true"]').on('submit', function(e) {
        e.preventDefault();
        // ... tu lógica AJAX
    });
});
</script>
@stop
```

**Importante:** El script global solo previene el envío si no hay cambios. Si hay cambios, tu código AJAX existente se ejecuta normalmente.

## Implementación en Controladores

### Paso 1: Importar y usar el Trait

```php
namespace App\Http\Controllers;

use App\Http\Controllers\Traits\CheckForChanges;

class MiController extends Controller
{
    use CheckForChanges;
    
    // ...
}
```

### Paso 2: Usar `updateIfChanged()` en el método `update()`

```php
public function update(Request $request, $id)
{
    $modelo = Modelo::findOrFail($id);
    
    $validatedData = $request->validate([
        'campo1' => 'required|string',
        'campo2' => 'required|integer',
        // ... validaciones
    ]);
    
    return $this->updateIfChanged($modelo, $validatedData, [
        'success_redirect' => route('modelo.index'),
        'success_message' => 'Registro actualizado correctamente.',
        'no_changes_message' => 'No se realizaron cambios.',
        'set_updated_by' => true,
        'use_transaction' => false, // true si necesitas transacciones
        'normalize_data' => false, // true para normalización automática
    ]);
}
```

**Opciones disponibles:**
- `success_redirect` - Ruta de redirección en caso de éxito
- `success_message` - Mensaje de éxito
- `no_changes_message` - Mensaje cuando no hay cambios
- `set_updated_by` - Establecer `updated_by` automáticamente (default: true)
- `use_transaction` - Usar transacciones de DB (default: false)
- `normalize_data` - Normalizar datos antes de comparar (default: false)

## Checklist para Nuevas Vistas Edit

### Frontend (Vista Blade)

- [ ] Agregar `data-edit-check="true"` al tag `<form>`
- [ ] Agregar `data-exclude-fields="_token,_method"` (y otros campos que no deben compararse)
- [ ] Incluir `form-edit-check.js` en la sección `@section('js')`
- [ ] Incluir SweetAlert2 si no está ya incluido
- [ ] Mantener la lógica AJAX existente (el script global no la reemplaza)

### Backend (Controlador)

- [ ] Importar `use App\Http\Controllers\Traits\CheckForChanges;`
- [ ] Agregar `use CheckForChanges;` en la clase del controlador
- [ ] Reemplazar la lógica `isDirty()` manual por `updateIfChanged()`
- [ ] Configurar las opciones según las necesidades del módulo

### Casos Especiales

#### Campos Calculados Automáticamente

Si tienes campos que se calculan automáticamente (ej: `Codigo_MP` en ingresos de MP), asegúrate de:
- Incluirlos en `data-exclude-fields` si no deben compararse
- O actualizar su valor antes de la comparación en el JS

#### Campos Disabled/Readonly

El script global automáticamente ignora campos `disabled` y `readonly` (excepto `hidden`). No necesitas hacer nada adicional.

#### Checkboxes y Radios

El script global maneja automáticamente checkboxes y radios. Para checkboxes, compara `'1'` (checked) vs `'0'` (unchecked).

#### Normalización de Datos

Si necesitas normalizar datos antes de comparar (ej: trim, formato numérico), puedes:
1. Usar `'normalize_data' => true` en el Trait (normalización básica)
2. O normalizar manualmente antes de llamar a `updateIfChanged()`

## Archivos Modificados

### Nuevos Archivos Creados

1. `public/js/form-edit-check.js` - Script global de detección de cambios
2. `app/Http/Controllers/Traits/CheckForChanges.php` - Trait para controladores
3. `PATRON_EDIT_FORMULARIOS.md` - Este documento

### Vistas Edit Actualizadas

1. `resources/views/marcas_insumos/edit.blade.php`
2. `resources/views/proveedores/edit.blade.php`
3. `resources/views/materia_prima/materias_base/edit.blade.php`
4. `resources/views/materia_prima/diametro/edit.blade.php`
5. `resources/views/materia_prima/ingresos/edit.blade.php`
6. `resources/views/materia_prima/egresos/edit.blade.php`
7. `resources/views/pedido_cliente/edit.blade.php`
8. `resources/views/roles/edit.blade.php`
9. `resources/views/permissions/edit.blade.php`
10. `resources/views/users/edit.blade.php`
11. `resources/views/fabricacion/edit.blade.php`

### Controladores Actualizados

1. `app/Http/Controllers/MarcasInsumosController.php`
2. `app/Http/Controllers/ProveedorController.php`
3. `app/Http/Controllers/MpIngresoController.php`

## Ejemplo Completo

### Vista Edit

```blade
@extends('adminlte::page')

@section('content')
<form action="{{ route('modelo.update', $modelo->id) }}" method="POST" 
      data-edit-check="true" 
      data-exclude-fields="_token,_method">
    @csrf
    @method('PUT')
    
    <div class="form-group">
        <label for="nombre">Nombre:</label>
        <input type="text" class="form-control" id="nombre" name="nombre" 
               value="{{ $modelo->nombre }}" required>
    </div>
    
    <div class="form-group">
        <label for="estado">Estado:</label>
        <select name="reg_Status" id="reg_Status" class="form-control">
            <option value="1" {{ $modelo->reg_Status == 1 ? 'selected' : '' }}>Activo</option>
            <option value="0" {{ $modelo->reg_Status == 0 ? 'selected' : '' }}>Inactivo</option>
        </select>
    </div>
    
    <button type="submit" class="btn btn-primary">Actualizar</button>
</form>
@stop

@section('js')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/form-edit-check.js') }}"></script>
<script>
$(document).ready(function() {
    $('form[data-edit-check="true"]').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        var $form = $(this);
        
        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: formData,
            success: function(response) {
                Swal.fire({
                    position: 'center',
                    icon: 'success',
                    title: 'Registro actualizado correctamente',
                    showConfirmButton: false,
                    timer: 1500
                });
                setTimeout(function() {
                    window.location.href = "{{ route('modelo.index') }}";
                }, 1500);
            },
            error: function(response) {
                Swal.fire({
                    position: 'center',
                    icon: 'error',
                    title: 'Ocurrió un error al actualizar',
                    showConfirmButton: true,
                });
            }
        });
    });
});
</script>
@stop
```

### Controlador

```php
<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\CheckForChanges;
use App\Models\Modelo;
use Illuminate\Http\Request;

class ModeloController extends Controller
{
    use CheckForChanges;
    
    public function update(Request $request, $id)
    {
        $modelo = Modelo::findOrFail($id);
        
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'reg_Status' => 'required|in:0,1',
        ]);
        
        return $this->updateIfChanged($modelo, $validatedData, [
            'success_redirect' => route('modelo.index'),
            'success_message' => 'Registro actualizado correctamente.',
            'no_changes_message' => 'No se realizaron cambios.',
            'set_updated_by' => true,
        ]);
    }
}
```

## Notas Importantes

1. **El script global NO reemplaza tu lógica AJAX**: Solo previene el envío si no hay cambios. Si hay cambios, tu código AJAX se ejecuta normalmente.

2. **Campos hidden**: Los campos `hidden` siempre se incluyen en la comparación, incluso si están `disabled` o `readonly`.

3. **Checkboxes**: Se comparan como `'1'` (checked) vs `'0'` (unchecked).

4. **Selects múltiples**: Se comparan como strings ordenados separados por comas.

5. **Normalización**: Si necesitas normalización específica, hazla manualmente antes de llamar a `updateIfChanged()`.

6. **Transacciones**: Si usas `'use_transaction' => true`, el Trait maneja automáticamente `beginTransaction()`, `commit()` y `rollBack()`.

## Soporte

Para cualquier duda o problema con este patrón, consulta:
- Este documento
- Los ejemplos en las vistas edit existentes
- El código fuente de `form-edit-check.js` y `CheckForChanges.php`

