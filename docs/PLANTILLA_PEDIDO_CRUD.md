# PLANTILLA PEDIDO CRUD

Usar este formato para pedirme modulos nuevos o migraciones.

---

## Pedido base

```text
Crear o migrar CRUD para tabla: NOMBRE_TABLA

Referencia visual:
- usar como base proveedores y marcas_insumos
- index/show deben seguir el patron visual unificado
- usar shared css siempre que sea posible

Datos tecnicos:
- tabla:
- primary key:
- usa soft delete: si/no
- columnas principales:
- columnas de estado:
- columnas de auditoria:

Relaciones:
- campo_local -> tabla_referenciada.campo_referenciado
- campo_local -> tabla_referenciada.campo_referenciado

Alcance:
- index
- show
- create
- edit
- deleted
- controlador
- rutas
- css especifico minimo

Notas:
- si hay filtros por select, cargarlos por ajax o definirlos
- si hay resumen, mostrar total / activos / eliminados
- si hay usuarios de auditoria, mostrarlos en show
```

---

## Pedido corto

Si queres algo mas rapido, alcanza con esto:

```text
Necesito CRUD para tabla: htal_alta
PK: Id_Herramienta
Relaciones:
- Id_Proveedor -> proveedores.Prov_Id
- Id_Marca -> marcas_insumos.Id_Marca
- created_by / updated_by / deleted_by -> users.id

Usar patron visual unificado del repo.
Referencia: proveedores + marcas_insumos.
```

---

## Regla de trabajo

Si no se aclara lo contrario, asumir:

- patron visual unificado
- `index` con cards resumen + DataTable + filtros
- `show` con `shared/show-details.css`
- `Ver / Editar / Eliminar`
- auditoria visible si existe en tabla
