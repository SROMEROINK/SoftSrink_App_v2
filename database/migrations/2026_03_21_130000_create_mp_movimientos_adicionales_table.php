<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mp_movimientos_adicionales', function (Blueprint $table) {
            $table->increments('Id_Movimiento_MP');
            $table->date('Fecha_Movimiento')->nullable();
            $table->string('Mes', 30)->nullable();
            $table->smallInteger('Anio')->nullable();
            $table->unsignedSmallInteger('Nro_Ingreso_MP')->nullable();
            $table->string('Concatenar_Proveedor')->nullable();
            $table->string('Materia_Prima')->nullable();
            $table->string('Diametro_MP', 100)->nullable();
            $table->string('Nro_Certificado_MP')->nullable();
            $table->unsignedSmallInteger('Nro_OF')->nullable();
            $table->string('Codigo_Producto')->nullable();
            $table->string('Nro_Maquina', 50)->nullable();
            $table->unsignedSmallInteger('Cantidad_Adicionales')->default(0);
            $table->unsignedSmallInteger('Cantidad_Devoluciones')->default(0);
            $table->decimal('Longitud_Unidad_Mts', 10, 2)->nullable();
            $table->decimal('Total_Mtros_Movimiento', 12, 2)->default(0);
            $table->unsignedBigInteger('Autorizado_por')->nullable();
            $table->text('Observaciones')->nullable();
            $table->tinyInteger('reg_Status')->default(1);
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->index('Nro_Ingreso_MP', 'idx_mov_adic_ingreso');
            $table->index('Nro_OF', 'idx_mov_adic_of');
            $table->index('Fecha_Movimiento', 'idx_mov_adic_fecha');
            $table->foreign('Autorizado_por', 'fk_mov_adic_autorizado_por')->references('id')->on('users')->nullOnDelete();
            $table->foreign('created_by', 'fk_mov_adic_created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by', 'fk_mov_adic_updated_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('deleted_by', 'fk_mov_adic_deleted_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mp_movimientos_adicionales');
    }
};
