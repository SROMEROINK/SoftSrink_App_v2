<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $foreignKeyExists = DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', 'mp_salidas_iniciales')
            ->where('CONSTRAINT_NAME', 'fk_mp_salidas_iniciales_ingreso')
            ->exists();

        DB::statement("
            ALTER TABLE mp_salidas_iniciales
            MODIFY Id_Ingreso_MP SMALLINT(5) NOT NULL
            COMMENT 'Referencia al ingreso de materia prima.'
        ");

        DB::statement("
            INSERT INTO mp_salidas_iniciales (
                Id_Ingreso_MP,
                Cantidad_Unidades_MP,
                Cantidad_Unidades_MP_Preparadas,
                Cantidad_MP_Adicionales,
                Total_Salidas_MP,
                Devoluciones_Unidades_MP,
                Total_Unidades,
                Longitud_Unidad_MP,
                Total_mm_Utilizados,
                reg_Status,
                created_at,
                created_by,
                updated_at,
                updated_by,
                deleted_at,
                deleted_by
            )
            SELECT
                i.Id_MP,
                COALESCE(i.Unidades_MP, 0),
                0,
                0,
                0,
                0,
                COALESCE(i.Unidades_MP, 0),
                COALESCE(i.Longitud_Unidad_MP, 0),
                0,
                b'1',
                COALESCE(i.created_at, NOW()),
                i.created_by,
                COALESCE(i.updated_at, NOW()),
                i.updated_by,
                NULL,
                NULL
            FROM mp_ingreso i
            LEFT JOIN mp_salidas_iniciales si ON si.Id_Ingreso_MP = i.Id_MP
            WHERE i.deleted_at IS NULL
              AND si.Id_Ingreso_MP IS NULL
        ");

        if (!$foreignKeyExists) {
            DB::statement("
                ALTER TABLE mp_salidas_iniciales
                ADD CONSTRAINT fk_mp_salidas_iniciales_ingreso
                FOREIGN KEY (Id_Ingreso_MP)
                REFERENCES mp_ingreso (Id_MP)
                ON UPDATE CASCADE
                ON DELETE RESTRICT
            ");
        }
    }

    public function down(): void
    {
        $foreignKeyExists = DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', 'mp_salidas_iniciales')
            ->where('CONSTRAINT_NAME', 'fk_mp_salidas_iniciales_ingreso')
            ->exists();

        if ($foreignKeyExists) {
            DB::statement("
                ALTER TABLE mp_salidas_iniciales
                DROP FOREIGN KEY fk_mp_salidas_iniciales_ingreso
            ");
        }

        DB::statement("
            ALTER TABLE mp_salidas_iniciales
            MODIFY Id_Ingreso_MP SMALLINT(5) NOT NULL AUTO_INCREMENT
            COMMENT 'Id de Producto,numero generado automaticamente,cada nuevo ingreso de un producto.'
        ");
    }
};
