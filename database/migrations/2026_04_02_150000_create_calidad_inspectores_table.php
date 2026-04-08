<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calidad_inspectores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 255)->unique();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        $historicos = DB::table('listado_entregas_productos')
            ->select('Inspector_Calidad')
            ->whereNotNull('Inspector_Calidad')
            ->where('Inspector_Calidad', '<>', '')
            ->distinct()
            ->orderBy('Inspector_Calidad')
            ->pluck('Inspector_Calidad');

        $now = now();
        foreach ($historicos as $nombre) {
            DB::table('calidad_inspectores')->updateOrInsert(
                ['nombre' => trim((string) $nombre)],
                [
                    'activo' => trim((string) $nombre) !== 'Sin registrar',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('calidad_inspectores');
    }
};
