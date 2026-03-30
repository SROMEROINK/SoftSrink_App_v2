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

        if (!in_array('Stock_Inicial', $columns, true)) {
            DB::statement("
                ALTER TABLE mp_salidas_iniciales
                ADD COLUMN Stock_Inicial SMALLINT(5) NOT NULL DEFAULT 0
                AFTER Id_Ingreso_MP
            ");
        }

        DB::statement("
            UPDATE mp_salidas_iniciales si
            INNER JOIN mp_ingreso i ON i.Id_MP = si.Id_Ingreso_MP
            SET si.Stock_Inicial = COALESCE(i.Unidades_MP, 0) - COALESCE(si.Devoluciones_Proveedor, 0)
        ");

        $dropColumns = [
            'Cantidad_Unidades_MP',
            'Cantidad_Unidades_MP_Preparadas',
            'Cantidad_MP_Adicionales',
            'Devoluciones_Unidades_MP',
            'Total_Unidades',
            'Longitud_Unidad_MP',
        ];

        foreach ($dropColumns as $column) {
            if (in_array($column, $columns, true)) {
                DB::statement("ALTER TABLE mp_salidas_iniciales DROP COLUMN {$column}");
            }
        }
    }

    public function down(): void
    {
        $columns = DB::table('information_schema.COLUMNS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', 'mp_salidas_iniciales')
            ->pluck('COLUMN_NAME')
            ->all();

        $addStatements = [
            'Cantidad_Unidades_MP' => "ADD COLUMN Cantidad_Unidades_MP SMALLINT(5) NOT NULL DEFAULT 0 AFTER Ajuste_Stock",
            'Cantidad_Unidades_MP_Preparadas' => "ADD COLUMN Cantidad_Unidades_MP_Preparadas SMALLINT(5) NOT NULL DEFAULT 0 AFTER Cantidad_Unidades_MP",
            'Cantidad_MP_Adicionales' => "ADD COLUMN Cantidad_MP_Adicionales SMALLINT(5) NOT NULL DEFAULT 0 AFTER Cantidad_Unidades_MP_Preparadas",
            'Devoluciones_Unidades_MP' => "ADD COLUMN Devoluciones_Unidades_MP SMALLINT(5) NOT NULL DEFAULT 0 AFTER Total_Salidas_MP",
            'Total_Unidades' => "ADD COLUMN Total_Unidades SMALLINT(5) NOT NULL DEFAULT 0 AFTER Devoluciones_Unidades_MP",
            'Longitud_Unidad_MP' => "ADD COLUMN Longitud_Unidad_MP DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER Total_Unidades",
        ];

        foreach ($addStatements as $column => $statement) {
            if (!in_array($column, $columns, true)) {
                DB::statement("ALTER TABLE mp_salidas_iniciales {$statement}");
            }
        }

        if (in_array('Stock_Inicial', $columns, true)) {
            DB::statement('ALTER TABLE mp_salidas_iniciales DROP COLUMN Stock_Inicial');
        }
    }
};
