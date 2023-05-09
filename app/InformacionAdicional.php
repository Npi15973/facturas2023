<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InformacionAdicional extends Model
{
    protected $table="informacion_adicional";

    protected $fillable=[
        'id',
        'nombre',
        'valor',
        'created_at',
        'updated_at',
        'tipo_documdento',
        'documento_id',
    ];

}
