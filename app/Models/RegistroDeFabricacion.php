<?php
// app\Models\RegistroDeFabricacion.php

namespace App\Models;

        use Illuminate\Database\Eloquent\Factories\HasFactory;
        use Illuminate\Database\Eloquent\Model;
        use App\Models\Listado_OF;
        use App\Models\Producto;

        class RegistroDeFabricacion extends Model
        {
            use HasFactory;
            protected $table = 'registro_de_fabricacion';
            protected $primaryKey = 'Id_OF';
            protected $fillable = [

            "Id_OF",
            "Nro_OF",
            "Id_Producto",
            "Nro_Parcial",
            "Nro_OF_Parcial",
            "Cant_Piezas",
            "Fecha_Fabricacion",
            "Horario",
            "Nombre_Operario",
            "Turno",
            "Cant_Horas_Extras",
            "Ultima_Carga",
            "Status_OF"
        ];

        public $timestamps = false;

        public function listado_of()
        {
            // La clave foránea en Listado_OF: 'Producto_Id'
            // La clave primaria en producto: 'Id_Producto'
            return $this->belongsTo(Listado_OF::class, 'Nro_OF','Id_OF'); // Cambia el nombre del modelo a Producto y especifica correctamente el namespace
        }


        }
