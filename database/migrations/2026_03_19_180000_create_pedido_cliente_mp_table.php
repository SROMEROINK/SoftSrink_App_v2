<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedido_cliente_mp', function (Blueprint $table) {
            $table->increments('Id_Pedido_MP');
            $table->smallInteger('Id_OF');
            $table->tinyInteger('Estado_Plani_Id')->default(11);
            $table->string('Codigo_MP')->nullable();
            $table->string('Materia_Prima')->nullable();
            $table->string('Diametro_MP', 100)->nullable();
            $table->unsignedSmallInteger('Nro_Ingreso_MP')->nullable();
            $table->unsignedSmallInteger('Nro_Ingreso_MP_DB')->nullable();
            $table->unsignedSmallInteger('Pedido_Material_Nro')->nullable();
            $table->string('Nro_Certificado_MP')->nullable();
            $table->decimal('Longitud_Un_MP', 10, 2)->nullable();
            $table->decimal('Largo_Pieza', 10, 2)->nullable();
            $table->decimal('Frenteado', 10, 2)->nullable();
            $table->decimal('Ancho_Cut_Off', 10, 2)->nullable();
            $table->decimal('Sobrematerial_Promedio', 10, 2)->nullable();
            $table->decimal('Largo_Total_Pieza', 10, 2)->nullable();
            $table->decimal('MM_Totales', 12, 2)->nullable();
            $table->decimal('Longitud_Barra_Sin_Scrap', 12, 2)->nullable();
            $table->unsignedInteger('Cant_Barras_MP')->nullable();
            $table->decimal('Cant_Piezas_Por_Barra', 10, 2)->nullable();
            $table->text('Observaciones')->nullable();
            $table->boolean('reg_Status')->default(1);
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->unique('Id_OF', 'uq_pedido_mp_of');
            $table->index('Estado_Plani_Id', 'idx_pedido_mp_estado');

            $table->foreign('Id_OF', 'fk_pedido_mp_of')
                ->references('Id_OF')->on('pedido_cliente')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('Estado_Plani_Id', 'fk_pedido_mp_estado')
                ->references('Estado_Plani_Id')->on('estado_planificacion')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('created_by', 'fk_pedido_mp_created_by')
                ->references('id')->on('users')
                ->nullOnDelete();

            $table->foreign('updated_by', 'fk_pedido_mp_updated_by')
                ->references('id')->on('users')
                ->nullOnDelete();

            $table->foreign('deleted_by', 'fk_pedido_mp_deleted_by')
                ->references('id')->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedido_cliente_mp');
    }
};
