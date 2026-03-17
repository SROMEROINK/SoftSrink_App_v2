<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private function fkExists(string $table, string $fk): bool {
        return DB::table('information_schema.REFERENTIAL_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $table)
            ->where('CONSTRAINT_NAME', $fk)
            ->exists();
    }
    private function indexExists(string $table, string $index): bool {
        return DB::table('information_schema.STATISTICS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $table)
            ->where('INDEX_NAME', $index)
            ->exists();
    }

    public function up(): void
    {
        // Alinear tipo a SMALLINT (SIGNED)
        DB::statement("ALTER TABLE fechas_of MODIFY Id_OF SMALLINT NULL");

        // (opcional) relación 1:1 — agrega unique si no existe
        if (! $this->indexExists('fechas_of', 'uq_fechas_idof')) {
            Schema::table('fechas_of', function (Blueprint $t) {
                $t->unique('Id_OF', 'uq_fechas_idof');
            });
        }

        // Crear FK si no existe
        if (! $this->fkExists('fechas_of', 'fechas_of_pedido_fk')) {
            Schema::table('fechas_of', function (Blueprint $t) {
                $t->foreign('Id_OF', 'fechas_of_pedido_fk')
                  ->references('Id_OF')->on('pedido_cliente')
                  ->onUpdate('cascade')->onDelete('restrict');
            });
        }
    }

    public function down(): void
    {
        if ($this->fkExists('fechas_of', 'fechas_of_pedido_fk')) {
            Schema::table('fechas_of', function (Blueprint $t) {
                $t->dropForeign('fechas_of_pedido_fk');
            });
        }
        if ($this->indexExists('fechas_of', 'uq_fechas_idof')) {
            Schema::table('fechas_of', function (Blueprint $t) {
                $t->dropUnique('uq_fechas_idof');
            });
        }
    }
};
