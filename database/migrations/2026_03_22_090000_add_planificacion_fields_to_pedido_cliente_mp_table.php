<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedido_cliente_mp', function (Blueprint $table) {
            if (!Schema::hasColumn('pedido_cliente_mp', 'Fecha_Planificacion')) {
                $table->date('Fecha_Planificacion')->nullable()->after('Cant_Barras_MP');
            }

            if (!Schema::hasColumn('pedido_cliente_mp', 'Responsable_Planificacion')) {
                $table->string('Responsable_Planificacion', 255)->nullable()->after('Fecha_Planificacion');
            }
        });

        DB::statement("
            UPDATE pedido_cliente_mp pm
            INNER JOIN mp_salidas s ON s.Id_OF_Salidas_MP = pm.Id_OF AND s.deleted_at IS NULL
            SET
                pm.Fecha_Planificacion = COALESCE(pm.Fecha_Planificacion, s.Fecha_del_Pedido_Produccion),
                pm.Responsable_Planificacion = COALESCE(NULLIF(pm.Responsable_Planificacion, ''), s.Responsable_Pedido_Produccion),
                pm.Pedido_Material_Nro = COALESCE(NULLIF(pm.Pedido_Material_Nro, ''), CAST(s.Nro_Pedido_MP AS CHAR))
        ");
    }

    public function down(): void
    {
        Schema::table('pedido_cliente_mp', function (Blueprint $table) {
            if (Schema::hasColumn('pedido_cliente_mp', 'Responsable_Planificacion')) {
                $table->dropColumn('Responsable_Planificacion');
            }

            if (Schema::hasColumn('pedido_cliente_mp', 'Fecha_Planificacion')) {
                $table->dropColumn('Fecha_Planificacion');
            }
        });
    }
};
