# SoftSrink_App_v2 – Project Rules

## Contexto del proyecto
Este proyecto es un sistema interno tipo ERP desarrollado en Laravel 11.

Stack tecnológico:
- Laravel 11
- AdminLTE 3
- Laravel Breeze (auth)
- Spatie Permission (roles y permisos)
- SweetAlert2 (confirmaciones)
- Yajra DataTables
- MySQL (InnoDB)
- Gestión de DB con HeidiSQL

No introducir nuevas tecnologías sin aprobación explícita.

---

## Arquitectura
- Controladores delgados
- Validaciones en FormRequest cuando aplique
- Lógica compleja en Services o Actions
- Modelos Eloquent con:
  - $primaryKey explícita
  - $timestamps definidos según tabla real

No inventar columnas ni relaciones.

---

## Autenticación y permisos
- Todas las rutas internas deben usar:
  - auth
  - permission:* (Spatie Permission)

Cada feature nueva debe definir su permission.

---

## DataTables
- Evitar N+1 usando with()
- select() explícito
- Columnas calculadas en backend
- Ordenamiento coherente con el usuario final

---

## UI / UX
- Uso consistente de AdminLTE
- Confirmaciones con SweetAlert2
- Mensajes claros y uniformes

---

## Base de datos
- MySQL + InnoDB
- Respetar nombres reales de columnas (Id_*, Nro_*)
- No modificar estructura sin analizar impacto
- Proponer migraciones o scripts SQL seguros

---

## Debug
- Usar Log::info(), warning(), error()
- No dejar logs permanentes sin propósito

---

## Regla crítica
Nunca borrar, renombrar o modificar elementos críticos sin:
1. Explicar impacto
2. Listar usos
3. Proponer plan seguro
