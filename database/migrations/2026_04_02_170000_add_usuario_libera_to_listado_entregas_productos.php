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
            if (!Schema::hasColumn('listado_entregas_productos', 'Id_Usuario_Libera')) {
                $table->unsignedBigInteger('Id_Usuario_Libera')->nullable()->after('Id_Inspector_Calidad');
            }
        });

        DB::table('listado_entregas_productos')
            ->whereNull('Id_Usuario_Libera')
            ->whereNotNull('created_by')
            ->update([
                'Id_Usuario_Libera' => DB::raw('created_by'),
            ]);

        $foreignExists = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('TABLE_NAME', 'listado_entregas_productos')
            ->where('COLUMN_NAME', 'Id_Usuario_Libera')
            ->where('CONSTRAINT_NAME', 'listado_entregas_productos_id_usuario_libera_foreign')
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->exists();

        if (!$foreignExists) {
            Schema::table('listado_entregas_productos', function (Blueprint $table) {
                $table->foreign('Id_Usuario_Libera', 'listado_entregas_productos_id_usuario_libera_foreign')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete()
                    ->cascadeOnUpdate();
            });
        }
    }

    public function down(): void
    {
        Schema::table('listado_entregas_productos', function (Blueprint $table) {
            try {
                $table->dropForeign('listado_entregas_productos_id_usuario_libera_foreign');
            } catch (Throwable $e) {
            }

            if (Schema::hasColumn('listado_entregas_productos', 'Id_Usuario_Libera')) {
                $table->dropColumn('Id_Usuario_Libera');
            }
        });
    }
};
