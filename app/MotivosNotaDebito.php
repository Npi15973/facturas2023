<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MotivosNotaDebito extends Model
{
    protected $table = "motivos_nota_debito";


    protected $fillable = [
        'id',
        'razon',
        'valor',
        'nota_debito_id',
        'created_at',
        'updated_at',
    ];
}
