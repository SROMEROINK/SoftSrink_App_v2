<?php

// database\migrations\2024_06_06_172156_add_audit_fields_to_tables.php
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
        'estado_planificación',
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
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                // Agregar timestamps si no existen
                if (!Schema::hasColumns($table->getTable(), ['created_at', 'updated_at'])) {
                    $table->timestamps();
                }

                // Agregar soft deletes si no existen
                if (!Schema::hasColumn($table->getTable(), 'deleted_at')) {
                    $table->softDeletes();
                }

                // Agregar columnas de auditoría sin restricciones de clave externa
                if (!Schema::hasColumn($table->getTable(), 'created_by')) {
                    $table->unsignedBigInteger('created_by')->nullable()->after('created_at');
                }
                if (!Schema::hasColumn($table->getTable(), 'updated_by')) {
                    $table->unsignedBigInteger('updated_by')->nullable()->after('updated_at');
                }
                if (!Schema::hasColumn($table->getTable(), 'deleted_by')) {
                    $table->unsignedBigInteger('deleted_by')->nullable()->after('deleted_at');
                }
            });
        }

        // Eliminar claves externas si ya existen
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                try {
                    $table->dropForeign([$table->getTable() . '_created_by_foreign']);
                } catch (\Exception $e) {}
                try {
                    $table->dropForeign([$table->getTable() . '_updated_by_foreign']);
                } catch (\Exception $e) {}
                try {
                    $table->dropForeign([$table->getTable() . '_deleted_by_foreign']);
                } catch (\Exception $e) {}
            });
        }

        // Agregar las restricciones de clave externa en una segunda pasada
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                if (Schema::hasColumn($table->getTable(), 'created_by')) {
                    $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
                }
                if (Schema::hasColumn($table->getTable(), 'updated_by')) {
                    $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
                }
                if (Schema::hasColumn($table->getTable(), 'deleted_by')) {
                    $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
                }
            });
        }
    }

    public function down()
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                if (Schema::hasColumn($table->getTable(), 'created_by')) {
                    $table->dropForeign([$table->getTable() . '_created_by_foreign']);
                    $table->dropColumn('created_by');
                }
                if (Schema::hasColumn($table->getTable(), 'updated_by')) {
                    $table->dropForeign([$table->getTable() . '_updated_by_foreign']);
                    $table->dropColumn('updated_by');
                }
                if (Schema::hasColumn($table->getTable(), 'deleted_by')) {
                    $table->dropForeign([$table->getTable() . '_deleted_by_foreign']);
                    $table->dropColumn('deleted_by');
                }
            });
        }
    }
}

