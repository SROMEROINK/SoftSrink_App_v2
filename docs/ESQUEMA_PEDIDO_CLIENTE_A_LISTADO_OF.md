# ESQUEMA PEDIDO CLIENTE A LISTADO OF

## Objetivo

Este documento define la arquitectura recomendada para pasar del pedido del cliente a:

- definicion de materia prima
- validacion de stock
- analisis de disponibilidad de fabricacion
- asignacion de maquina
- calculos tecnicos
- tiempos de P.A.P. y produccion
- salida real de materia prima
- consolidacion final en `listado_of`

La meta es dejar separadas las etapas del proceso para no mezclar:

- pedido comercial
- abastecimiento de MP
- capacidad de maquina
- stock real
- tiempos reales de produccion

---

## 1. Criterio general

No conviene resolver todo en una sola tabla ni en una sola vista.

La estructura recomendada es por etapas:

1. `pedido_cliente`
2. `pedido_cliente_mp`
3. `pedido_cliente_maquinas`
4. `mp_salidas`
5. `mp_salidas_movimientos`
6. `fechas_of`
7. `listado_of`

Cada modulo cumple una responsabilidad distinta.

---

## 2. Responsabilidad de cada tabla

### 2.1 `pedido_cliente`

Es la entrada base del pedido.

Debe guardar solamente:

- `Id_OF`
- `Nro_OF`
- `Producto_Id`
- `Fecha_del_Pedido`
- `Cant_Fabricacion`
- `Estado_Plani_Id`
- `reg_Status`
- auditoria

Esta tabla responde:

- que pidio el cliente
- cuando lo pidio
- cuantas unidades hay que fabricar
- en que estado global esta la OF

No debe cargar toda la logica de MP, maquina, tiempos y scrap.

`Estado_Plani_Id` en `pedido_cliente` debe interpretarse como el estado global actual de la OF.

Las tablas posteriores de MP y maquina pueden tener sus propios estados operativos, pero el pedido base necesita este estado visible desde el inicio.

---

### 2.2 `pedido_cliente_mp`

Nueva tabla recomendada.

Esta tabla representa unicamente la etapa de abastecimiento y definicion de materia prima.

Debe guardar:

- estado del pedido respecto de MP
- MP seleccionada
- diametro
- codigo MP
- ingreso MP asignado
- certificado MP
- longitud unitaria de barra
- calculos de necesidad de material
- pedido de material si no hay stock

Esta tabla responde:

- con que MP se va a fabricar
- si hay stock o no
- cuanto material se necesita
- si hay que pedir material

No debe definir maquina ni tiempos de produccion.

---

### 2.3 `pedido_cliente_maquinas`

Nueva tabla recomendada.

Esta tabla representa la etapa posterior a MP, cuando ya se analiza disponibilidad de fabricacion y se asigna la maquina.

En la implementacion actual validada, como una OF solo puede pasar por una maquina, conviene dejar esta tabla minimalista y normalizada.

Debe guardar:

- `Id_Pedido_Maquina`
- `Id_OF`
- `Id_Pedido_MP`
- `Id_Maquina`
- `reg_Status`
- auditoria

La descripcion visible de la maquina debe salir desde `maquinas_produc`.

Esta tabla responde:

- en que maquina se va a fabricar
- cual es la referencia tecnica unica de maquina por OF

No debe duplicar `Nro_Maquina`, `Familia_Maquina` ni los datos madre de MP.

---

### 2.4 `mp_salidas`

Debe seguir siendo la tabla de salida real de materia prima.

No se recomienda usarla como tabla de calculo preliminar.

Debe reflejar:

- lo que efectivamente se entrego
- fecha de pedido a produccion
- responsable
- pedido de material
- cantidades entregadas y preparadas

Esta tabla responde:

- que salio realmente del stock

---

### 2.5 `mp_salidas_movimientos`

Nueva tabla recomendada.

Se usa para no mezclar en `mp_salidas`:

- salida inicial
- adicionales
- devoluciones

Permite trazabilidad fina por OF.

---

### 2.6 `fechas_of`

Debe representar la etapa real o confirmada de tiempos de produccion.

Conviene usarla para:

- inicio y fin de P.A.P.
- inicio y fin de OF
- tiempo pieza real
- tiempos historicos

No conviene meter aqui toda la logica de materia prima ni la asignacion preliminar de maquina.

---

### 2.7 `listado_of`

Es la vista o tabla consolidada final de consulta operativa.

Debe nutrirse de:

- `pedido_cliente`
- `pedido_cliente_mp`
- `pedido_cliente_maquinas`
- `mp_salidas`
- `fechas_of`

Su objetivo es mostrar el panorama unificado, no ser la fuente principal de carga.

En la implementacion final validada:

- `listado_of` debe ser `VIEW`, no tabla fisica de carga manual
- maquina y familia se resuelven desde `pedido_cliente_maquinas` + `maquinas_produc`
- `Codigo_MP`, `Nro_Certificado_MP`, `Nro_Pedido_MP`, `Nro_Remito_MP`, `Fecha_Ingreso_MP` y `Prov_Nombre` deben salir de `mp_ingreso`
- `pedido_cliente_mp` queda como fuente operativa de planificacion, pero la descripcion final de MP en el consolidado debe salir de `mp_ingreso`

---

## 3. Tabla nueva recomendada: `pedido_cliente_mp`

### 3.1 Campos manuales

- `Id_Pedido_MP`
- `Id_OF`
- `Estado_Plani_Id`
- `Codigo_MP`
- `Materia_Prima`
- `Diametro_MP`
- `Nro_Ingreso_MP`
- `Pedido_Material_Nro`
- `Nro_Certificado_MP`
- `Longitud_Un_MP`
- `Observaciones`
- `reg_Status`
- auditoria

### 3.2 Campos derivados

- `Largo_Pieza`
- `Frenteado`
- `Ancho_Cut_Off`
- `Sobrematerial_Promedio`
- `Largo_Total_Pieza`
- `MM_Totales`
- `Longitud_Barra_Sin_Scrap`
- `Cant_Barras_MP`
- `Cant_Piezas_Por_Barra`

### 3.3 Relaciones

- `Id_OF -> pedido_cliente.Id_OF`
- `Estado_Plani_Id -> estado_planificacion.Estado_Plani_Id`

Si mas adelante se formaliza una tabla de ingresos MP relacionada, tambien convendra:


---

## 4. SQL base sugerido para `pedido_cliente_mp`

```sql
CREATE TABLE `pedido_cliente_mp` (
    `Id_Pedido_MP` INT NOT NULL AUTO_INCREMENT,
    `Id_OF` SMALLINT NOT NULL,
    `Estado_Plani_Id` INT NOT NULL,
    `Codigo_MP` VARCHAR(255) NULL,
    `Materia_Prima` VARCHAR(255) NULL,
    `Diametro_MP` VARCHAR(100) NULL,
    `Nro_Ingreso_MP` SMALLINT NULL,
    `Pedido_Material_Nro` SMALLINT NULL,
    `Nro_Certificado_MP` VARCHAR(255) NULL,
    `Longitud_Un_MP` DECIMAL(10,2) NULL,
    `Largo_Pieza` DECIMAL(10,2) NULL,
    `Frenteado` DECIMAL(10,2) NULL,
    `Ancho_Cut_Off` DECIMAL(10,2) NULL,
    `Sobrematerial_Promedio` DECIMAL(10,2) NULL,
    `Largo_Total_Pieza` DECIMAL(10,2) NULL,
    `MM_Totales` DECIMAL(12,2) NULL,
    `Longitud_Barra_Sin_Scrap` DECIMAL(12,2) NULL,
    `Cant_Barras_MP` INT NULL,
    `Cant_Piezas_Por_Barra` DECIMAL(10,2) NULL,
    `Observaciones` TEXT NULL,
    `reg_Status` BIT(1) NOT NULL DEFAULT b'1',
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `created_by` BIGINT UNSIGNED NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_by` BIGINT UNSIGNED NULL,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    `deleted_by` BIGINT UNSIGNED NULL,
    PRIMARY KEY (`Id_Pedido_MP`),
    UNIQUE KEY `uq_pedido_mp_of` (`Id_OF`),
    KEY `idx_pedido_mp_estado` (`Estado_Plani_Id`),
    CONSTRAINT `fk_pedido_mp_of`
        FOREIGN KEY (`Id_OF`) REFERENCES `pedido_cliente` (`Id_OF`),
    CONSTRAINT `fk_pedido_mp_estado`
        FOREIGN KEY (`Estado_Plani_Id`) REFERENCES `estado_planificacion` (`Estado_Plani_Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
```

---

## 5. Tabla nueva recomendada: `pedido_cliente_maquina`

### 5.1 Campos manuales

- `Id_Pedido_Maquina`
- `Id_OF`
- `Estado_Plani_Id`
- `Id_Maquina`
- `Nro_Maquina`
- `Familia_Maquina`
- `Scrap_Barra_MM`
- `T_Pieza_Aprox`
- `T_Pieza_Real`
- `Inicio_PAP`
- `Hora_Inicio_PAP`
- `Fin_PAP`
- `Hora_Fin_PAP`
- `Inicio_Produccion`
- `Fin_Produccion`
- `Mes_Produccion`
- `Anio_Produccion`
- `Observaciones`
- `reg_Status`
- auditoria

### 5.2 Relaciones

- `Id_OF -> pedido_cliente.Id_OF`
- `Estado_Plani_Id -> estado_planificacion.Estado_Plani_Id`
- `Id_Maquina -> maquinas_produc.id_maquina`

### 5.3 SQL base sugerido para `pedido_cliente_maquina`

```sql
CREATE TABLE `pedido_cliente_maquina` (
    `Id_Pedido_Maquina` INT NOT NULL AUTO_INCREMENT,
    `Id_OF` SMALLINT NOT NULL,
    `Estado_Plani_Id` INT NOT NULL,
    `Id_Maquina` INT NULL,
    `Nro_Maquina` VARCHAR(50) NULL,
    `Familia_Maquina` VARCHAR(100) NULL,
    `Scrap_Barra_MM` DECIMAL(10,2) NULL,
    `T_Pieza_Aprox` DECIMAL(10,2) NULL,
    `T_Pieza_Real` DECIMAL(10,2) NULL,
    `Inicio_PAP` DATE NULL,
    `Hora_Inicio_PAP` TIME NULL,
    `Fin_PAP` DATE NULL,
    `Hora_Fin_PAP` TIME NULL,
    `Inicio_Produccion` DATE NULL,
    `Fin_Produccion` DATE NULL,
    `Mes_Produccion` VARCHAR(30) NULL,
    `Anio_Produccion` SMALLINT NULL,
    `Observaciones` TEXT NULL,
    `reg_Status` BIT(1) NOT NULL DEFAULT b'1',
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `created_by` BIGINT UNSIGNED NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_by` BIGINT UNSIGNED NULL,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    `deleted_by` BIGINT UNSIGNED NULL,
    PRIMARY KEY (`Id_Pedido_Maquina`),
    UNIQUE KEY `uq_pedido_maquina_of` (`Id_OF`),
    KEY `idx_pedido_maquina_estado` (`Estado_Plani_Id`),
    KEY `idx_pedido_maquina_maquina` (`Id_Maquina`),
    CONSTRAINT `fk_pedido_maquina_of`
        FOREIGN KEY (`Id_OF`) REFERENCES `pedido_cliente` (`Id_OF`),
    CONSTRAINT `fk_pedido_maquina_estado`
        FOREIGN KEY (`Estado_Plani_Id`) REFERENCES `estado_planificacion` (`Estado_Plani_Id`),
    CONSTRAINT `fk_pedido_maquina_maquina`
        FOREIGN KEY (`Id_Maquina`) REFERENCES `maquinas_produc` (`id_maquina`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
```

---

## 6. Tabla nueva recomendada: `mp_salidas_movimientos`

```sql
CREATE TABLE `mp_salidas_movimientos` (
    `Id_Movimiento` INT NOT NULL AUTO_INCREMENT,
    `Id_Egresos_MP` SMALLINT NOT NULL,
    `Id_OF` SMALLINT NOT NULL,
    `Tipo_Movimiento` ENUM('SALIDA_INICIAL','ADICIONAL','DEVOLUCION') NOT NULL,
    `Cantidad_Barras` INT NOT NULL DEFAULT 0,
    `Longitud_Mts` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `Observacion` VARCHAR(255) NULL,
    `Fecha_Movimiento` DATE NOT NULL,
    `reg_Status` BIT(1) NOT NULL DEFAULT b'1',
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `created_by` BIGINT UNSIGNED NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_by` BIGINT UNSIGNED NULL,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    `deleted_by` BIGINT UNSIGNED NULL,
    PRIMARY KEY (`Id_Movimiento`),
    KEY `idx_movimiento_egreso` (`Id_Egresos_MP`),
    KEY `idx_movimiento_of` (`Id_OF`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
```

Si se desea, luego se le pueden agregar foreign keys segun como quede formalizado el modulo de egresos.

---

## 7. Formulas a replicar en sistema

Estas cuentas no deberian vivir dispersas por controladores.

Conviene centralizarlas en servicios, por ejemplo:

- `app/Services/PedidoClienteMpCalculator.php`
- `app/Services/PedidoClienteMaquinaCalculator.php`

### 7.1 Largo total de pieza

```text
Largo_Total_Pieza = Largo_Pieza + Frenteado + Ancho_Cut_Off + Sobrematerial_Promedio
```

### 7.2 mm totales

```text
MM_Totales = Cant_Fabricacion * Largo_Total_Pieza
```

### 7.3 Longitud de barra sin scrap

```text
Longitud_Barra_Sin_Scrap = Longitud_Un_MP - Scrap_Barra_MM_REFERENCIA
```

En la etapa de MP conviene usar:

- `Scrap_Barra_MM_REFERENCIA`

que puede venir:

- de una maquina sugerida
- del minimo scrap esperado
- o quedar pendiente hasta asignar maquina real

### 7.4 Cantidad de barras de MP

```text
Cant_Barras_MP = ceil(MM_Totales / Longitud_Barra_Sin_Scrap)
```

Si no hay ingreso definido, se puede usar una longitud por defecto de barra para la simulacion.

### 7.5 Cantidad de piezas por barra

```text
Cant_Piezas_Por_Barra = ceil(Longitud_Barra_Sin_Scrap / Largo_Total_Pieza)
```

### 7.6 Scrap por barra real

```text
Scrap_Barra_MM = maquinas_produc.scrap_maquina
```

Esto debe quedar fijado cuando la OF ya tiene maquina asignada.

### 7.7 Mes y ano de produccion

Derivados desde `Inicio_Produccion`.

---

## 8. Flujo recomendado de pantallas

### Etapa 1. Pedido del cliente

Vista:

- `pedido_cliente/index`
- `pedido_cliente/create`
- `pedido_cliente/edit`

Objetivo:

- alta de OF
- producto
- fecha del pedido
- cantidad a fabricar

Estado inicial recomendado:

- `NUEVO PEDIDO`

---

### Etapa 2. Abastecimiento / MP

Vista nueva recomendada:

- `pedido_cliente_mp/index`
- `pedido_cliente_mp/edit`

Objetivo:

- definir estado de planificacion respecto de MP
- elegir MP
- validar stock
- elegir ingreso MP
- calcular barras necesarias
- definir si hace falta pedido de material

Estados posibles:

- `EN ANALISIS DE STOCK`
- `PENDIENTE DE MATERIA PRIMA`
- `LISTA PARA ASIGNAR MAQUINA`

---

### Etapa 3. Disponibilidad de maquina y tiempos

Vista nueva recomendada:

- `pedido_cliente_maquina/index`
- `pedido_cliente_maquina/edit`

Objetivo:

- validar disponibilidad de fabricacion
- definir maquina
- tomar `scrap_maquina`
- cargar P.A.P.
- proyectar o confirmar tiempos

Estados posibles:

- `PENDIENTE DE MAQUINA`
- `A PLANIFICAR`
- `EN PRODUCCION`

---

### Etapa 4. Salida real de MP

Modulo existente:

- `mp_salidas`

Objetivo:

- registrar lo que efectivamente se entrego
- guardar pedido de material
- responsable
- cantidades preparadas

Modulo complementario sugerido:

- `mp_salidas_movimientos`

para:

- adicionales
- devoluciones

---

### Etapa 5. Tiempos reales de OF

Modulo existente:

- `fechas_of`

Objetivo:

- guardar tiempos finales o reales
- comparar con estimaciones
- medir productividad

---

### Etapa 6. Consolidado final

Modulo final:

- `listado_of`

Objetivo:

- mostrar el estado global de cada OF
- consolidar pedido, MP, maquina y tiempos

---

## 9. Regla clave para no pisar modulos

No mezclar:

- simulacion de necesidad de MP
- analisis de capacidad de maquina
- salida real de MP

Separacion recomendada:

- `pedido_cliente_mp` = necesidad calculada de materia prima
- `pedido_cliente_maquina` = disponibilidad, maquina y tiempos
- `mp_salidas` = entrega real
- `mp_salidas_movimientos` = extras y devoluciones

Esto evita que las vistas de `Egreso de Materia Prima` queden contaminadas con logica de planificacion.

---

## 10. Observaciones tecnicas actuales

### 10.1 `fechas_of`

La tabla real usa:

- `Nro_OF_fechas`

pero el modelo actual [FechasOf.php](C:/laragon/www/SoftSrink_App_v2/app/Models/FechasOf.php) todavia intenta relacionar por:

- `Id_OF`

Antes de integrar de lleno `fechas_of` al circuito nuevo, convendra corregir esa relacion.

### 10.2 `pedido_cliente`

La tabla real ya no tiene:

- `Estado_Plani_Id`

Por lo tanto, el estado de planificacion no debe volver a colocarse en `pedido_cliente`; debe vivir en las tablas nuevas de MP y/o maquina.

---

## 11. Orden recomendado de implementacion

1. Cerrar visual y funcionalmente `pedido_cliente/create`, `edit`, `show`
2. Crear tabla `pedido_cliente_mp`
3. Crear modelo, controlador, rutas y vistas de abastecimiento MP
4. Implementar servicio de calculo de MP
5. Integrar stock disponible de MP
6. Crear tabla `pedido_cliente_maquina`
7. Crear modelo, controlador, rutas y vistas de maquina y tiempos
8. Implementar calculos de capacidad, scrap y tiempos
9. Recien despues acoplar con `fechas_of`
10. Finalmente armar el consolidado `listado_of`

---

## 12. Decision recomendada

La mejor arquitectura para este proyecto es:

- separar pedido base, MP y maquina en etapas distintas
- mantener `mp_salidas` solo para movimientos reales
- usar `fechas_of` para tiempos reales o confirmados
- centralizar formulas en servicios
- usar `listado_of` como salida consolidada final

Esta separacion reduce errores y evita que una sola vista termine haciendo demasiadas cosas a la vez.
