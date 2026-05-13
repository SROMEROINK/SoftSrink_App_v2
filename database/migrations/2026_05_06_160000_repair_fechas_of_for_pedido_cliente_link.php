<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private const EMPTY_DATE = '9999-12-31';
    private const EMPTY_TIME = '00:00:00';

    private function columnExists(string $table, string $column): bool
    {
        return Schema::hasColumn($table, $column);
    }

    private function fkExists(string $table, string $fk): bool
    {
        return DB::table('information_schema.REFERENTIAL_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $table)
            ->where('CONSTRAINT_NAME', $fk)
            ->exists();
    }

    private function indexExists(string $table, string $index): bool
    {
        return DB::table('information_schema.STATISTICS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $table)
            ->where('INDEX_NAME', $index)
            ->exists();
    }

    public function up(): void
    {
        if (! $this->columnExists('fechas_of', 'Id_OF')) {
            Schema::table('fechas_of', function (Blueprint $table) {
                $table->smallInteger('Id_OF')->nullable()->after('Id_Fechas');
            });
        } else {
            DB::statement("ALTER TABLE fechas_of MODIFY Id_OF SMALLINT NULL");
        }

        if (! $this->columnExists('fechas_of', 'Tiempo_Seg')) {
            Schema::table('fechas_of', function (Blueprint $table) {
                $table->integer('Tiempo_Seg')->default(0)->after('Tiempo_Pieza');
            });
        }

        if ($this->fkExists('fechas_of', 'fechas_of_pedido_fk')) {
            Schema::table('fechas_of', function (Blueprint $table) {
                $table->dropForeign('fechas_of_pedido_fk');
            });
        }

        if ($this->indexExists('fechas_of', 'uq_fechas_idof')) {
            Schema::table('fechas_of', function (Blueprint $table) {
                $table->dropUnique('uq_fechas_idof');
            });
        }

        // Reinicio operativo: se reconstruye la tabla a partir de pedido_cliente.
        DB::table('fechas_of')->delete();

        DB::statement("
            INSERT INTO fechas_of (
                Id_OF,
                Nro_OF_fechas,
                Nro_Programa_H1,
                Nro_Programa_H2,
                Inicio_PAP,
                Hora_Inicio_PAP,
                Fin_PAP,
                Hora_Fin_PAP,
                Inicio_OF,
                Finalizacion_OF,
                Tiempo_Pieza,
                Tiempo_Seg,
                reg_Status,
                created_at,
                created_by,
                updated_at,
                updated_by
            )
            SELECT
                p.Id_OF,
                p.Nro_OF,
                NULL,
                NULL,
                '" . self::EMPTY_DATE . "',
                '" . self::EMPTY_TIME . "',
                '" . self::EMPTY_DATE . "',
                '" . self::EMPTY_TIME . "',
                '" . self::EMPTY_DATE . "',
                '" . self::EMPTY_DATE . "',
                0.00,
                0,
                b'1',
                NOW(),
                p.created_by,
                NOW(),
                COALESCE(p.updated_by, p.created_by)
            FROM pedido_cliente p
            WHERE p.deleted_at IS NULL
            ORDER BY p.Nro_OF
        ");

        if (! $this->indexExists('fechas_of', 'uq_fechas_idof')) {
            Schema::table('fechas_of', function (Blueprint $table) {
                $table->unique('Id_OF', 'uq_fechas_idof');
            });
        }

        if (! $this->fkExists('fechas_of', 'fechas_of_pedido_fk')) {
            Schema::table('fechas_of', function (Blueprint $table) {
                $table->foreign('Id_OF', 'fechas_of_pedido_fk')
                    ->references('Id_OF')
                    ->on('pedido_cliente')
                    ->onUpdate('cascade')
                    ->onDelete('restrict');
            });
        }
    }

    public function down(): void
    {
        if ($this->fkExists('fechas_of', 'fechas_of_pedido_fk')) {
            Schema::table('fechas_of', function (Blueprint $table) {
                $table->dropForeign('fechas_of_pedido_fk');
            });
        }

        if ($this->indexExists('fechas_of', 'uq_fechas_idof')) {
            Schema::table('fechas_of', function (Blueprint $table) {
                $table->dropUnique('uq_fechas_idof');
            });
        }

        if ($this->columnExists('fechas_of', 'Id_OF')) {
            Schema::table('fechas_of', function (Blueprint $table) {
                $table->dropColumn('Id_OF');
            });
        }
    }
};
