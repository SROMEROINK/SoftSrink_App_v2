# PATRON MODELOS SOFTSRINK

## Objetivo

Este documento define el patrón recomendado para los modelos Eloquent dentro de `SoftSrink_App_v2`.

Busca unificar:

- estructura base de los modelos
- uso de `SoftDeletes`
- definición de `$table`
- definición de `$primaryKey`
- uso de `timestamps`
- definición de `$fillable`
- definición de `$casts`
- relaciones entre tablas
- trazabilidad con usuarios (`created_by`, `updated_by`, `deleted_by`)

---

# 1. Rol del modelo en SoftSrink

En SoftSrink, cada modelo debe representar fielmente la tabla real de base de datos.

Un modelo bien armado debe dejar claro:

- qué tabla usa
- cuál es su clave primaria
- qué campos pueden asignarse masivamente
- qué relaciones tiene
- si usa o no borrado lógico
- si usa o no auditoría de usuario

---

# 2. Estructura base recomendada

## Patrón general

```php id="k3q4op"
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NombreModelo extends Model
{
    use SoftDeletes;

    protected $table = 'nombre_tabla';
    protected $primaryKey = 'Id_Modelo';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'Campo_1',
        'Campo_2',
        'reg_Status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'reg_Status' => 'integer',
    ];
}

Notas! 08/03/2026:

3. namespace

Todos los modelos del proyecto deben usar:

namespace App\Models;
4. Uso de SoftDeletes
Cuándo usarlo

Usar SoftDeletes cuando el módulo necesite:

recuperar registros eliminados

mantener historial

evitar borrado físico

soportar vista deleted.blade.php

soportar restore()

Cómo declararlo
use Illuminate\Database\Eloquent\SoftDeletes;

y dentro de la clase:

use SoftDeletes;
Requisito de base de datos

La tabla debe tener:

deleted_at

y si se trabaja con auditoría:

deleted_by
5. $table

Siempre declarar explícitamente la tabla cuando no siga exactamente la convención Laravel.

Ejemplos reales de SoftSrink
protected $table = 'mp_ingreso';
protected $table = 'pedido_cliente';
protected $table = 'estado_planificacion';
Motivo

SoftSrink usa muchos nombres personalizados, así que conviene no dejarlo implícito.

6. $primaryKey

En muchas tablas del proyecto la clave primaria no es id.

Por eso hay que declararla siempre cuando sea distinta.

Ejemplos
protected $primaryKey = 'Id_MP';
protected $primaryKey = 'Id_OF';
protected $primaryKey = 'Estado_Plani_Id';
7. $incrementing

Usar:

public $incrementing = true;

cuando la PK sea autoincremental.

Si no lo es, revisar si corresponde:

public $incrementing = false;
8. $keyType

Cuando la PK sea numérica, usar:

protected $keyType = 'int';

Si fuese string, ajustar según corresponda.

9. public $timestamps = true

En SoftSrink, la mayoría de las tablas modernas usan:

created_at

updated_at

Entonces conviene declarar:

public $timestamps = true;
Cuándo usar false

Solo si la tabla no tiene esos campos o si la lógica lo exige explícitamente.

10. $fillable

Todo modelo debe listar explícitamente qué campos acepta por asignación masiva.

Ejemplo base
protected $fillable = [
    'Campo_1',
    'Campo_2',
    'reg_Status',
    'created_by',
    'updated_by',
    'deleted_by',
];
Regla

Si el controlador usa Model::create($validatedData), los campos deben estar en $fillable.

11. Qué suele entrar en $fillable

En SoftSrink normalmente entran:

Campos del negocio

nombre

descripción

estado

fechas

códigos

cantidades

claves foráneas

Campos de trazabilidad

created_by

updated_by

deleted_by

A veces también

reg_Status

campos de observación

campos calculados persistidos

12. $casts

Usar $casts cuando querés normalizar tipos y evitar errores.

Ejemplos útiles
protected $casts = [
    'reg_Status' => 'integer',
    'Status' => 'integer',
    'Fecha_Ingreso' => 'date',
    'Fecha_del_Pedido' => 'date',
    'Cant_Fabricacion' => 'integer',
];
13. Cuándo conviene usar casts
Muy recomendable en:

estados numéricos

booleanos

fechas

enteros

algunos decimales si la lógica lo necesita

Beneficios

comparación más limpia

menos errores en isDirty()

mejor consistencia entre backend y frontend

14. Relaciones belongsTo

Cuando la tabla tiene una FK que apunta a otra tabla, conviene usar belongsTo().

Ejemplo
public function proveedor()
{
    return $this->belongsTo(Proveedor::class, 'Id_Proveedor', 'Prov_Id');
}
Regla

Siempre indicar:

modelo relacionado

FK local

PK remota

15. Relaciones hasMany

Cuando un registro padre tiene varios hijos.

Ejemplo
public function ingresos()
{
    return $this->hasMany(MpIngreso::class, 'Id_Materia_Prima', 'Id_Materia_Prima');
}
16. Relaciones de auditoría

Si el modelo trabaja con usuarios, conviene declarar estas relaciones:

public function creator()
{
    return $this->belongsTo(User::class, 'created_by');
}

public function updater()
{
    return $this->belongsTo(User::class, 'updated_by');
}

public function deleter()
{
    return $this->belongsTo(User::class, 'deleted_by');
}
Requisito

Importar:

use App\Models\User;

si el modelo lo necesita.

17. Ejemplo real tipo catálogo simple
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EstadoPlanificacion extends Model
{
    use SoftDeletes;

    protected $table = 'estado_planificacion';
    protected $primaryKey = 'Estado_Plani_Id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'Nombre_Estado',
        'Status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'Status' => 'integer',
    ];
}
18. Ejemplo real tipo técnico con relaciones
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MpIngreso extends Model
{
    use SoftDeletes;

    protected $table = 'mp_ingreso';
    protected $primaryKey = 'Id_MP';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'Nro_Ingreso_MP',
        'Nro_Pedido',
        'Nro_Remito',
        'Fecha_Ingreso',
        'Nro_OC',
        'Id_Proveedor',
        'Id_Materia_Prima',
        'Id_Diametro_MP',
        'Codigo_MP',
        'Nro_Certificado_MP',
        'Detalle_Origen_MP',
        'Unidades_MP',
        'Longitud_Unidad_MP',
        'Mts_Totales',
        'Kilos_Totales',
        'reg_Status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'Fecha_Ingreso' => 'date',
        'reg_Status' => 'integer',
    ];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'Id_Proveedor', 'Prov_Id');
    }

    public function materiaPrima()
    {
        return $this->belongsTo(MpMateriaPrima::class, 'Id_Materia_Prima', 'Id_Materia_Prima');
    }

    public function diametro()
    {
        return $this->belongsTo(MpDiametro::class, 'Id_Diametro_MP', 'Id_Diametro');
    }
}
19. Reglas para módulos simples

En módulos simples, el modelo normalmente necesita:

SoftDeletes

PK personalizada

timestamps

fillable

casts básicos

opcionalmente relaciones de usuario

Ejemplos:

EstadoPlanificacion

MarcasInsumos

Proveedor

MpDiametro

20. Reglas para módulos multifila

En módulos multifila, el modelo sigue siendo uno por tabla, pero el controlador lo usará varias veces en un foreach.

El modelo debe estar listo para:

create() repetido

relaciones técnicas

casts de fechas y cantidades

auditoría

Ejemplos:

PedidoCliente

MpIngreso

RegistroDeFabricacion

21. Regla sobre reg_Status

Muchos módulos usan reg_Status como activo/inactivo.

Recomendación

Agregarlo al modelo:

protected $casts = [
    'reg_Status' => 'integer',
];

Y, si se usa mucho, considerar helpers o accesores en el futuro.

22. Reglas sobre constantes

Cuando el modelo tiene estados fijos, puede ser útil usar constantes.

Ejemplo
public const ACTIVO = 1;
public const INACTIVO = 0;

o en estados de planificación:

public const SIN_REALIZAR = 1;
public const EN_PROCESO   = 2;
public const FINALIZADA   = 3;
Cuándo conviene

cuando el ID tiene significado de negocio fijo

cuando querés evitar “números mágicos” en controladores

23. Errores comunes a evitar
1. No declarar $table

Si la tabla tiene nombre no convencional, Laravel puede buscar otra tabla.

2. No declarar $primaryKey

Muchos módulos del proyecto no usan id.

3. No usar SoftDeletes

Puede borrar físicamente registros que debían quedar recuperables.

4. Olvidar campos en $fillable

Hace que el create() o update() no persistan todo.

5. No declarar relaciones

Complica DataTables, shows y formularios.

6. No usar casts

Genera inconsistencias en fechas, estados y comparaciones.

7. No alinear modelo con la tabla real

Es una fuente clásica de errores en Laravel.

24. Buenas prácticas
Sí hacer

declarar todo explícitamente

revisar tabla real antes de crear el modelo

mantener relaciones limpias

usar nombres consistentes

usar SoftDeletes si el módulo lo requiere

incluir trazabilidad

No hacer

dejar que Laravel “adivine” demasiado

mezclar nombres inconsistentes

usar relaciones sin revisar las FKs reales

copiar modelos viejos sin adaptar bien tabla y PK

25. Checklist rápido por modelo nuevo

Antes de cerrar un modelo, verificar:

 namespace correcto

 use SoftDeletes si corresponde

 $table correcto

 $primaryKey correcto

 $incrementing correcto

 $keyType correcto

 public $timestamps correcto

 $fillable completo

 $casts útiles definidos

 relaciones correctas

 campos de auditoría contemplados

26. Relación con otros patrones

Este documento debe leerse junto con:

PATRON_CONTROLADORES_SOFTSRINK.md

PATRON_CRUD_SIMPLE_SOFTSRINK.md

PATRON_CRUD_MULTIFILA_SOFTSRINK.md

PATRON_RUTAS_SOFTSRINK.md

CHECKLIST_NUEVO_MODULO.md

27. Conclusión

En SoftSrink_App_v2, un modelo bien definido debe cumplir estas reglas:

representar exactamente la tabla real

declarar claramente la PK

soportar auditoría y timestamps

usar SoftDeletes cuando aplique

definir relaciones limpias

ser predecible para controladores, vistas y DataTables

Por eso, cada modelo nuevo debería construirse siguiendo este patrón antes de avanzar con controlador, rutas y vistas.