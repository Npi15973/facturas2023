<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Emisor extends Model
{
    
    protected $table = "emisor";


    protected $fillable = [
        'id',
        'ruc',
        'razon_social',
        'nombre_comercial',
        'direccion_matriz',
        'ambiente',
        'tipo_emision',
        'contribuyente',
        'obligado_contabilidad',
        'password_firma',
        'firma',
        'correo_remitente',
        'resolucion_agente_retencion',
        'logo',
        'company_id',
        'created_at',
        'updated_at',
        'is_active',
        'regimen',
        'serie'
    ];
}
