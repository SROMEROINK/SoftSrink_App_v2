CREATE OR REPLACE VIEW listado_of_index_db AS
SELECT
    pc.Id_OF,
    pc.Nro_OF,
    ep.Nombre_Estado AS Estado_Planificacion,
    CASE
        WHEN pc.reg_Status = b'1' OR pc.reg_Status = 1 THEN 'Activo'
        ELSE 'Inactivo'
    END AS Estado,
    pr.Prod_Codigo,
    pr.Prod_Descripcion,
    cat.Nombre_Categoria,
    pr.Prod_N_Plano AS Revision_Plano_1,
    pr.Prod_Plano_Ultima_Revision AS Revision_Plano_2,
    pc.Fecha_del_Pedido,
    pc.Cant_Fabricacion,
    COALESCE(mq.Nro_maquina, pm.Nro_Maquina) AS Nro_Maquina,
    COALESCE(mq.familia_maquina, pm.Familia_Maquina) AS Familia_Maquinas,
    COALESCE(pm.Nro_Ingreso_MP, hist.Nro_Ingreso_MP_DB) AS Nro_Ingreso_MP,
    mp_src.Codigo_MP,
    mp_src.Nro_Certificado_MP,
    mp_src.Nro_Pedido AS Pedido_de_MP,
    prov.Prov_Nombre,
    COALESCE(rf.Piezas_Fabricadas, 0) AS Piezas_Fabricadas,
    COALESCE(pc.Cant_Fabricacion, 0) - COALESCE(rf.Piezas_Fabricadas, 0) AS Saldo_Fabricacion,
    COALESCE(ent.Piezas_Entregadas, 0) AS Piezas_Entregadas,
    CASE
        WHEN COALESCE(pc.Cant_Fabricacion, 0) > 0 THEN ROUND((COALESCE(rf.Piezas_Fabricadas, 0) * 100.0) / pc.Cant_Fabricacion, 2)
        ELSE 0
    END AS Porcentaje_Fabricado,
    CASE
        WHEN COALESCE(pc.Cant_Fabricacion, 0) > 0 THEN ROUND((COALESCE(ent.Piezas_Entregadas, 0) * 100.0) / pc.Cant_Fabricacion, 2)
        ELSE 0
    END AS Porcentaje_Entregado,
    COALESCE(rf.Piezas_Fabricadas, 0) - COALESCE(ent.Piezas_Entregadas, 0) AS Saldo_Entrega,
    CASE
        WHEN COALESCE(ent.Piezas_Entregadas, 0) > COALESCE(rf.Piezas_Fabricadas, 0)
            THEN CONCAT('Entregado de mas: ', FORMAT(COALESCE(ent.Piezas_Entregadas, 0) - COALESCE(rf.Piezas_Fabricadas, 0), 0, 'de_DE'))
        WHEN COALESCE(rf.Piezas_Fabricadas, 0) > COALESCE(ent.Piezas_Entregadas, 0)
            THEN CONCAT('Restan entregar: ', FORMAT(COALESCE(rf.Piezas_Fabricadas, 0) - COALESCE(ent.Piezas_Entregadas, 0), 0, 'de_DE'))
        WHEN COALESCE(rf.Piezas_Fabricadas, 0) = 0 AND COALESCE(ent.Piezas_Entregadas, 0) = 0
            THEN 'Sin fabricacion ni entregas'
        ELSE 'Entrega completa'
    END AS Control_Entrega,
    rf.Ultima_Fecha_Fabricacion,
    ent.Ultima_Fecha_Entrega,
    pc.created_at,
    pc.updated_at,
    pm.Id_Pedido_MP
FROM pedido_cliente pc
LEFT JOIN estado_planificacion ep
    ON ep.Estado_Plani_Id = pc.Estado_Plani_Id
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
LEFT JOIN (
    SELECT
        Nro_OF,
        SUM(Cant_Piezas) AS Piezas_Fabricadas,
        MAX(Fecha_Fabricacion) AS Ultima_Fecha_Fabricacion
    FROM registro_de_fabricacion
    WHERE deleted_at IS NULL
    GROUP BY Nro_OF
) rf
    ON rf.Nro_OF = pc.Id_OF
LEFT JOIN (
    SELECT
        Id_OF AS Nro_OF,
        SUM(Cant_Piezas_Entregadas) AS Piezas_Entregadas,
        MAX(Fecha_Entrega_Calidad) AS Ultima_Fecha_Entrega
    FROM listado_entregas_productos
    WHERE deleted_at IS NULL
    GROUP BY Id_OF
) ent
    ON ent.Nro_OF = pc.Nro_OF
WHERE pc.deleted_at IS NULL;
