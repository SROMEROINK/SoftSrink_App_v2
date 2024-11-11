<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateMpIngresoForeignKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mp_ingreso', function (Blueprint $table) {
            $table->unsignedSmallInteger('Id_Materia_Prima')->nullable()->change(); // Clave foránea para materia prima
            $table->unsignedSmallInteger('Id_Diametro')->nullable()->change(); // Clave foránea para diámetro

            // Añadir relaciones foráneas
            $table->foreign('Id_Materia_Prima')->references('Id_Materia_Prima')->on('mp_materia_prima')->onDelete('set null');
            $table->foreign('Id_Diametro')->references('Id_Diametro')->on('mp_diametro')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mp_ingreso', function (Blueprint $table) {
            $table->dropForeign(['Id_Materia_Prima']);
            $table->dropForeign(['Id_Diametro']);
            $table->unsignedSmallInteger('Id_Materia_Prima')->nullable(false)->change();
            $table->unsignedSmallInteger('Id_Diametro')->nullable(false)->change();
        });
    }
}
