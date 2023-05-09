<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Retencion extends Model
{
    protected $table="retenciones";

    protected $fillable=[
        'id',
        'ambiente',
        'sujeto_retenido',
        'tipo_sujeto',
        'clave_acceso',
        'estado_sri',
        'created_at',
        'updated_at',
        'warehouse_id',
        'secuencial_auxiliar',
        'numero_documento',
        'tipo_identificacion',
        'fecha_emision',
        'fecha_autorizacion',
        'periodo_fiscal',
        'user_id'
    ];
}
