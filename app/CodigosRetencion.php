<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CodigosRetencion extends Model
{
    protected $table ="codigo_retencion";
    protected $fillable=[
        'id',
        'tipo',
        'codigo',
        'porcentaje',
        'descripcion',
        'created_at',
        'updated_at',
    ];
}
