-- SoftSrink_App_v2
-- Normalizacion de nombres de columnas en la tabla productos
-- Recomendado: ejecutar sobre backup / copia antes de llevar a produccion

ALTER TABLE productos
DROP FOREIGN KEY fk_Productos_Clase,
DROP FOREIGN KEY fk_Productos_SubFamilia,
DROP FOREIGN KEY fk_Productos_GrupoSubcategoria,
DROP FOREIGN KEY fk_Productos_Conjuntos;

ALTER TABLE productos
DROP INDEX fk_Productos_Clase,
DROP INDEX fk_Productos_SubFamilia,
DROP INDEX fk_Productos_GrupoSubcategoria,
DROP INDEX fk_Productos_Conjuntos;

ALTER TABLE productos
CHANGE COLUMN Id_Prod_Clase_Familia Id_Prod_Categoria INT(10) NOT NULL DEFAULT '0' COMMENT 'Categoria del Producto',
CHANGE COLUMN Id_Prod_Sub_Familia Id_Prod_SubCategoria INT(10) NOT NULL DEFAULT '0' COMMENT 'Subcategoria del Producto',
CHANGE COLUMN Id_Prod_Grupos_de_Sub_Familia Id_Prod_GrupoSubcategoria INT(10) NOT NULL DEFAULT '0' COMMENT 'Grupo de Subcategoria del Producto',
CHANGE COLUMN Id_Prod_Codigo_Conjuntos Id_Prod_GrupoConjuntos INT(10) NOT NULL DEFAULT '0' COMMENT 'Grupo de Conjuntos del Producto',
CHANGE COLUMN `Prod_Plano_Ultima_Revisión` Prod_Plano_Ultima_Revision VARCHAR(50) NOT NULL DEFAULT '';

ALTER TABLE productos
ADD INDEX fk_productos_categoria (Id_Prod_Categoria),
ADD INDEX fk_productos_subcategoria (Id_Prod_SubCategoria),
ADD INDEX fk_productos_grupo_subcategoria (Id_Prod_GrupoSubcategoria),
ADD INDEX fk_productos_grupo_conjuntos (Id_Prod_GrupoConjuntos);

ALTER TABLE productos
ADD CONSTRAINT fk_productos_categoria
    FOREIGN KEY (Id_Prod_Categoria)
    REFERENCES producto_categoria (Id_Categoria)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
ADD CONSTRAINT fk_productos_subcategoria
    FOREIGN KEY (Id_Prod_SubCategoria)
    REFERENCES producto_subcategoria (Id_SubCategoria)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
ADD CONSTRAINT fk_productos_grupo_subcategoria
    FOREIGN KEY (Id_Prod_GrupoSubcategoria)
    REFERENCES producto_grupo_subcategoria (Id_GrupoSubCategoria)
    ON UPDATE CASCADE
    ON DELETE RESTRICT,
ADD CONSTRAINT fk_productos_grupo_conjuntos
    FOREIGN KEY (Id_Prod_GrupoConjuntos)
    REFERENCES producto_grupo_conjuntos (Id_GrupoConjuntos)
    ON UPDATE CASCADE
    ON DELETE RESTRICT;
