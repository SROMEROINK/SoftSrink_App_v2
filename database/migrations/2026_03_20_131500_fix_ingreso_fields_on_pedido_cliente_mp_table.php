
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE pedido_cliente_mp MODIFY Nro_Ingreso_MP_DB INT UNSIGNED NULL");
        DB::statement("ALTER TABLE pedido_cliente_mp MODIFY Pedido_Material_Nro VARCHAR(255) NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE pedido_cliente_mp MODIFY Nro_Ingreso_MP_DB SMALLINT UNSIGNED NULL");
        DB::statement("ALTER TABLE pedido_cliente_mp MODIFY Pedido_Material_Nro SMALLINT UNSIGNED NULL");
    }
};
