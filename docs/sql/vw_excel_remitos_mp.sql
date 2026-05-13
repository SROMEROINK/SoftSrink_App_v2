CREATE OR REPLACE VIEW vw_excel_remitos_mp AS
SELECT
    lep.Id_List_Entreg_Prod,
    lep.Id_OF AS Nro_OF,
    lep.Nro_Parcial_Calidad,
    lep.Nro_Remito_Entrega_Calidad,
    lep.Fecha_Entrega_Calidad,
    pr.Prod_Codigo AS Codigo_Producto,
    pr.Prod_Descripcion AS Descripcion,
    COALESCE(pm.Pedido_Material_Nro, mp_src.Nro_Pedido) AS Lote,
    COALESCE(pm.Materia_Prima, mp_mat.Nombre_Materia) AS Materia_Prima,
    COALESCE(pm.Nro_Certificado_MP, mp_src.Nro_Certificado_MP) AS Nro_Certificado_MP,
    COALESCE(pm.Diametro_MP, mp_dia.Valor_Diametro) AS Diametro_MP,
    lep.Cant_Piezas_Entregadas AS Cantidad,
    pm.Observaciones,
    COALESCE(pm.Codigo_MP, mp_src.Codigo_MP) AS Codigo_MP,
    COALESCE(pm.Nro_Maquina, mq.Nro_maquina) AS Nro_Maquina,
    COALESCE(pm.Familia_Maquina, mq.familia_maquina) AS Familia_Maquina,
    prov.Prov_Nombre AS Proveedor
FROM listado_entregas_productos AS lep
LEFT JOIN pedido_cliente AS pc
    ON pc.Nro_OF = lep.Id_OF
   AND pc.deleted_at IS NULL
LEFT JOIN productos AS pr
    ON pr.Id_Producto = pc.Producto_Id
   AND pr.deleted_at IS NULL
LEFT JOIN pedido_cliente_mp AS pm
    ON pm.Id_OF = pc.Id_OF
   AND pm.deleted_at IS NULL
LEFT JOIN pedido_cliente_maquinas AS pcm
    ON pcm.Id_OF = pc.Id_OF
   AND pcm.deleted_at IS NULL
LEFT JOIN maquinas_produc AS mq
    ON mq.id_maquina = pcm.Id_Maquina
LEFT JOIN mp_ingreso AS mp_src
    ON mp_src.Nro_Ingreso_MP = pm.Nro_Ingreso_MP
   AND mp_src.deleted_at IS NULL
LEFT JOIN mp_materia_prima AS mp_mat
    ON mp_mat.Id_Materia_Prima = mp_src.Id_Materia_Prima
   AND mp_mat.deleted_at IS NULL
LEFT JOIN mp_diametro AS mp_dia
    ON mp_dia.Id_Diametro = mp_src.Id_Diametro_MP
   AND mp_dia.deleted_at IS NULL
LEFT JOIN proveedores AS prov
    ON prov.Prov_Id = mp_src.Id_Proveedor
WHERE lep.deleted_at IS NULL;
