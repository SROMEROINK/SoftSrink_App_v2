<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedido_cliente_mp', function (Blueprint $table) {
            $table->unsignedInteger('Id_Maquina')->nullable()->after('Estado_Plani_Id');
            $table->string('Nro_Maquina', 50)->nullable()->after('Id_Maquina');
            $table->string('Familia_Maquina', 255)->nullable()->after('Nro_Maquina');
            $table->decimal('Scrap_Maquina', 10, 2)->nullable()->after('Familia_Maquina');

            $table->index('Id_Maquina', 'idx_pedido_mp_maquina');
        });
    }

    public function down(): void
    {
        Schema::table('pedido_cliente_mp', function (Blueprint $table) {
            $table->dropIndex('idx_pedido_mp_maquina');
            $table->dropColumn(['Id_Maquina', 'Nro_Maquina', 'Familia_Maquina', 'Scrap_Maquina']);
        });
    }
};
