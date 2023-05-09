<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DetalleRetencion extends Model
{
    protected $table="detalle_retencion";
    
    protected $fillable=[
        'id',
        'tipo_documento',
        'codigo_retencion',
        'porcentaje',
        'base_imponible',
        'total',
        'numero_documento',
        'tipo_impuesto',
        'fecha_documento',
        'retencion_id',
        'created_at',
        'updated_at',
    ];
}
