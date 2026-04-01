CREATE OR REPLACE VIEW listado_entregas_productos_db AS
SELECT
    lep.Id_List_Entreg_Prod,
    lep.Id_OF AS Nro_OF,
    lep.Nro_Parcial_Calidad,
    lep.Cant_Piezas_Entregadas,
    lep.Nro_Remito_Entrega_Calidad,
    lep.Fecha_Entrega_Calidad,
    lep.Inspector_Calidad,
    lep.reg_Status,
    lep.created_at,
    lep.created_by,
    lep.updated_at,
    lep.updated_by,
    lep.deleted_at,
    lep.deleted_by,
    pr.Prod_Codigo,
    pr.Prod_Descripcion,
    cat.Nombre_Categoria,
    COALESCE(mq.Nro_maquina, pm.Nro_Maquina) AS Nro_Maquina,
    COALESCE(mq.familia_maquina, pm.Familia_Maquina) AS Familia_Maquinas,
    COALESCE(pm.Nro_Ingreso_MP, hist.Nro_Ingreso_MP_DB) AS Nro_Ingreso_MP,
    mp_src.Codigo_MP,
    mp_src.Nro_Certificado_MP,
    mp_src.Nro_Pedido AS Pedido_de_MP,
    prov.Prov_Nombre,
    NULL AS Piezas_Fabricadas,
    NULL AS Saldo_Fabricacion,
    pc.Id_OF AS Pedido_Id,
    pm.Id_Pedido_MP
FROM listado_entregas_productos lep
LEFT JOIN pedido_cliente pc
    ON pc.Nro_OF = lep.Id_OF
   AND pc.deleted_at IS NULL
LEFT JOIN productos pr
    ON pr.Id_Producto = pc.Producto_Id
LEFT JOIN producto_categoria cat
    ON cat.Id_Categoria = pr.Id_Prod_Categoria
LEFT JOIN pedido_cliente_mp pm
    ON pm.Id_OF = pc.Id_OF
   AND pm.deleted_at IS NULL
LEFT JOIN pedido_cliente_maquinas pcm
    ON pcm.Id_OF = pc.Id_OF
   AND pcm.deleted_at IS NULL
LEFT JOIN maquinas_produc mq
    ON mq.id_maquina = pcm.Id_Maquina
LEFT JOIN tmp_pedido_cliente_maquinas_csv hist
    ON hist.Nro_OF = pc.Nro_OF
LEFT JOIN mp_ingreso mp_src
    ON mp_src.Nro_Ingreso_MP = COALESCE(pm.Nro_Ingreso_MP, hist.Nro_Ingreso_MP_DB)
   AND mp_src.deleted_at IS NULL
LEFT JOIN proveedores prov
    ON prov.Prov_Id = mp_src.Id_Proveedor
WHERE lep.deleted_at IS NULL;
