<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FormaDePago extends Model
{
    protected $table = "forma_pago_facturacion";


    protected $fillable = [
        'id',
        'codigo',
        'descripcion',
        'updated_at',
        'created_at'
    ];
}
