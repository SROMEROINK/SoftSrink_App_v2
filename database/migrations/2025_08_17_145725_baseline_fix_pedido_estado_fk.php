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

    public function up(): void
    {
        // Alinear tipo (SIGNED) sin requerir DBAL
        DB::statement("ALTER TABLE pedido_cliente MODIFY Estado_Plani_Id TINYINT NULL");

        // Crear FK si no existe
        if (! $this->fkExists('pedido_cliente', 'fk_pedido_estado')) {
            Schema::table('pedido_cliente', function (Blueprint $t) {
                $t->foreign('Estado_Plani_Id', 'fk_pedido_estado')
                  ->references('Estado_Plani_Id')->on('estado_planificacion')
                  ->onUpdate('cascade')->onDelete('restrict');
            });
        }
    }

    public function down(): void
    {
        if ($this->fkExists('pedido_cliente', 'fk_pedido_estado')) {
            Schema::table('pedido_cliente', function (Blueprint $t) {
                $t->dropForeign('fk_pedido_estado');
            });
        }
    }
};
