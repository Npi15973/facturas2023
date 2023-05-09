<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TipoDocumento extends Model
{
    
    protected $table = "tipo_documento";


    protected $fillable = [
        'id',
        'codigo',
        'descripcion',
        'updated_at',
        'created_at'
    ];
}
