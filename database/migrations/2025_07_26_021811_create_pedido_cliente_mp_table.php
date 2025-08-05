<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePedidoClienteMpTable extends Migration
{
    public function up()
    {
        Schema::create('pedido_cliente_mp', function (Blueprint $table) {
            $table->smallIncrements('Id_OF'); // AutoIncrement
            $table->smallInteger('Nro_OF')->unique()->nullable();
            $table->smallInteger('Producto_Id')->default(0);
            $table->integer('Nro_MP_Asignado')->nullable()->comment('Número de materia prima asignado');
            $table->date('Fecha_Asignacion_MP')->nullable()->comment('Fecha de asignación de la materia prima');
            $table->tinyInteger('Status_OF')->default(1)->comment('Estado MP 1=Habilitado, 0=Deshabilitado');
            
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            // Claves foráneas
            $table->foreign('Producto_Id')->references('Id_Producto')->on('productos');
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('deleted_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pedido_cliente_mp');
    }
}