<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE mp_salidas
            ADD COLUMN Id_Pedido_MP INT UNSIGNED NULL AFTER Id_OF_Salidas_MP,
            ADD INDEX idx_mp_salidas_pedido_mp (Id_Pedido_MP)
        ");

        DB::statement("
            ALTER TABLE mp_salidas
            ADD CONSTRAINT fk_mp_salidas_pedido_mp
            FOREIGN KEY (Id_Pedido_MP)
            REFERENCES pedido_cliente_mp (Id_Pedido_MP)
            ON UPDATE CASCADE
            ON DELETE RESTRICT
        ");

        DB::statement("
            ALTER TABLE mp_salidas
            MODIFY Fecha_del_Pedido_Produccion DATE NULL,
            MODIFY Responsable_Pedido_Produccion VARCHAR(255) NULL,
            MODIFY Nro_Pedido_MP SMALLINT(5) NULL,
            MODIFY Fecha_de_Entrega_Pedido_Calidad DATE NULL,
            MODIFY Responsable_de_entrega_Calidad VARCHAR(255) NULL
        ");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE mp_salidas DROP FOREIGN KEY fk_mp_salidas_pedido_mp");
        DB::statement("ALTER TABLE mp_salidas DROP INDEX idx_mp_salidas_pedido_mp");
        DB::statement("ALTER TABLE mp_salidas DROP COLUMN Id_Pedido_MP");
    }
};
