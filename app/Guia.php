<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Guia extends Model
{
    
    protected $table="guia";

    protected $fillable=[
        'id',
        'ambiente',
        'auxiliar_secuencial',
        'numero_documento',
        'warehouse_id',
        'user_id',
        'direccion_partida',
        'transportista',
        'identificacion_transportista',
        'ruc_transportista',
        'fecha_inicio',
        'fecha_fin',
        'placa',
        'customer_id',
        'motivo_traslado',
        'created_at',
        'updated_at',
        'clave_acceso',
        'estado_sri',
        'tipo_documento',
        'fecha_emision',
        'fecha_autorizacion'
    ];
}
