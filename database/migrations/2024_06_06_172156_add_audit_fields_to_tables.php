<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAuditFieldsToTables extends Migration
{
    protected $tables = [
        'caja_chica_ingresos',
        'caja_chica_resumen',
        'caja_chica_salidas',
        'carga_registro_de_fabricacion',
        'clientes',
        'datos_de_la_empresa',
        'estado_planificacion',
        'fechas_of',
        'htal_alta',
        'htal_devolucion',
        'htal_entradas',
        'htal_salidas_of',
        'htal_salidas_resumen',
        'htal_salidas_varios',
        'listado_entregas_facturacion',
        'listado_entregas_productos',
        'listado_of',
        'marcas_insumos',
        'mp_ingreso',
        'mp_precio',
        'mp_salidas',
        'mp_salidas_iniciales',
        'nc_calidad',
        'nc_cliente',
        'nc_produccion',
        'nc_recupero_calidad',
        'nc_recupero_cliente',
        'producto_categoria',
        'producto_tipo',
        'producto_grupo_conjuntos',
        'producto_grupo_subcategoria',
        'producto_subcategoria',
        'productos',
        'proveedores',
        'registro_de_fabricacion',
    ];

    public function up()
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumns($tableName, ['created_at', 'updated_at'])) {
                    $table->timestamps();
                }

                if (!Schema::hasColumn($tableName, 'deleted_at')) {
                    $table->softDeletes();
                }

                if (!Schema::hasColumn($tableName, 'created_by')) {
                    $table->unsignedBigInteger('created_by')->nullable()->after('created_at');
                }

                if (!Schema::hasColumn($tableName, 'updated_by')) {
                    $table->unsignedBigInteger('updated_by')->nullable()->after('updated_at');
                }

                if (!Schema::hasColumn($tableName, 'deleted_by')) {
                    $table->unsignedBigInteger('deleted_by')->nullable()->after('deleted_at');
                }
            });
        }

        // Intentar eliminar claves foráneas antiguas
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $foreignKeys = [
                    $tableName . '_created_by_foreign',
                    $tableName . '_updated_by_foreign',
                    $tableName . '_deleted_by_foreign',
                ];

                foreach ($foreignKeys as $fk) {
                    try {
                        $table->dropForeign($fk);
                    } catch (\Exception $e) {
                        // Silenciar si no existe
                    }
                }
            });
        }

        // Agregar claves foráneas nuevamente
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'created_by')) {
                    $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
                }

                if (Schema::hasColumn($tableName, 'updated_by')) {
                    $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
                }

                if (Schema::hasColumn($tableName, 'deleted_by')) {
                    $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
                }
            });
        }
    }

    public function down()
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'created_by')) {
                    try {
                        $table->dropForeign($tableName . '_created_by_foreign');
                    } catch (\Exception $e) {}
                    $table->dropColumn('created_by');
                }

                if (Schema::hasColumn($tableName, 'updated_by')) {
                    try {
                        $table->dropForeign($tableName . '_updated_by_foreign');
                    } catch (\Exception $e) {}
                    $table->dropColumn('updated_by');
                }

                if (Schema::hasColumn($tableName, 'deleted_by')) {
                    try {
                        $table->dropForeign($tableName . '_deleted_by_foreign');
                    } catch (\Exception $e) {}
                    $table->dropColumn('deleted_by');
                }
            });
        }
    }
}
