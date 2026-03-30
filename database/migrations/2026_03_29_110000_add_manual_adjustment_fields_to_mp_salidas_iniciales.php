<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $columns = DB::table('information_schema.COLUMNS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', 'mp_salidas_iniciales')
            ->pluck('COLUMN_NAME')
            ->all();

        if (!in_array('Devoluciones_Proveedor', $columns, true)) {
            DB::statement("
                ALTER TABLE mp_salidas_iniciales
                ADD COLUMN Devoluciones_Proveedor SMALLINT(5) NOT NULL DEFAULT 0
                AFTER Id_Ingreso_MP
            ");
        }

        if (!in_array('Ajuste_Stock', $columns, true)) {
            DB::statement("
                ALTER TABLE mp_salidas_iniciales
                ADD COLUMN Ajuste_Stock SMALLINT(6) NOT NULL DEFAULT 0
                AFTER Devoluciones_Proveedor
            ");
        }

        DB::statement("
            UPDATE mp_salidas_iniciales
            SET Devoluciones_Proveedor = COALESCE(Devoluciones_Proveedor, Devoluciones_Unidades_MP, 0)
        ");

        DB::statement("
            UPDATE mp_salidas_iniciales
            SET Ajuste_Stock = COALESCE(Ajuste_Stock, 0)
        ");
    }

    public function down(): void
    {
        $columns = DB::table('information_schema.COLUMNS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', 'mp_salidas_iniciales')
            ->pluck('COLUMN_NAME')
            ->all();

        if (in_array('Ajuste_Stock', $columns, true)) {
            DB::statement('ALTER TABLE mp_salidas_iniciales DROP COLUMN Ajuste_Stock');
        }

        if (in_array('Devoluciones_Proveedor', $columns, true)) {
            DB::statement('ALTER TABLE mp_salidas_iniciales DROP COLUMN Devoluciones_Proveedor');
        }
    }
};
