<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maquinas_produc', function (Blueprint $table) {
            $table->tinyInteger('Status')->default(1)->after('scrap_maquina');
        });

        DB::table('maquinas_produc')
            ->whereIn('id_maquina', [4, 5, 6, 12])
            ->update(['Status' => 0]);
    }

    public function down(): void
    {
        Schema::table('maquinas_produc', function (Blueprint $table) {
            $table->dropColumn('Status');
        });
    }
};
