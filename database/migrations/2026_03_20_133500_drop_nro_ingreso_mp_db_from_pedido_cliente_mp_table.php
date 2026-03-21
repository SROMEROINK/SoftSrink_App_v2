
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('pedido_cliente_mp', 'Nro_Ingreso_MP_DB')) {
            DB::statement("ALTER TABLE pedido_cliente_mp DROP COLUMN Nro_Ingreso_MP_DB");
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('pedido_cliente_mp', 'Nro_Ingreso_MP_DB')) {
            DB::statement("ALTER TABLE pedido_cliente_mp ADD Nro_Ingreso_MP_DB INT UNSIGNED NULL AFTER Nro_Ingreso_MP");
        }
    }
};
