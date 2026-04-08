<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listado_entregas_productos', function (Blueprint $table) {
            if (!Schema::hasColumn('listado_entregas_productos', 'Id_Inspector_Calidad')) {
                $table->unsignedBigInteger('Id_Inspector_Calidad')->nullable()->after('Inspector_Calidad');
            }
        });

        DB::table('listado_entregas_productos as lep')
            ->join('calidad_inspectores as ci', 'ci.nombre', '=', 'lep.Inspector_Calidad')
            ->whereNull('lep.Id_Inspector_Calidad')
            ->update([
                'lep.Id_Inspector_Calidad' => DB::raw('ci.id'),
            ]);

        $uniqueExists = DB::table('information_schema.STATISTICS')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('TABLE_NAME', 'listado_entregas_productos')
            ->where('INDEX_NAME', 'listado_entregas_productos_nro_parcial_calidad_unique')
            ->exists();

        if (!$uniqueExists) {
            Schema::table('listado_entregas_productos', function (Blueprint $table) {
                $table->unique('Nro_Parcial_Calidad', 'listado_entregas_productos_nro_parcial_calidad_unique');
            });
        }

        $foreignExists = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('TABLE_NAME', 'listado_entregas_productos')
            ->where('COLUMN_NAME', 'Id_Inspector_Calidad')
            ->where('CONSTRAINT_NAME', 'listado_entregas_productos_id_inspector_calidad_foreign')
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->exists();

        if (!$foreignExists) {
            Schema::table('listado_entregas_productos', function (Blueprint $table) {
                $table->foreign('Id_Inspector_Calidad', 'listado_entregas_productos_id_inspector_calidad_foreign')
                    ->references('id')
                    ->on('calidad_inspectores')
                    ->nullOnDelete()
                    ->cascadeOnUpdate();
            });
        }
    }

    public function down(): void
    {
        Schema::table('listado_entregas_productos', function (Blueprint $table) {
            try {
                $table->dropForeign('listado_entregas_productos_id_inspector_calidad_foreign');
            } catch (Throwable $e) {
            }

            try {
                $table->dropUnique('listado_entregas_productos_nro_parcial_calidad_unique');
            } catch (Throwable $e) {
            }

            if (Schema::hasColumn('listado_entregas_productos', 'Id_Inspector_Calidad')) {
                $table->dropColumn('Id_Inspector_Calidad');
            }
        });
    }
};
